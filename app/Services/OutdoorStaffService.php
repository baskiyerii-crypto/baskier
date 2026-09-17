<?php

namespace App\Services;

use App\Models\OohOccupancy;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class OutdoorStaffService
{
    public function ensureOwner(Vendor $vendor): VendorMember
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('vendor_members')) {
            throw new RuntimeException('Açık hava ekip tablosu henüz kurulmadı.');
        }

        $existing = VendorMember::query()
            ->where('vendor_id', $vendor->id)
            ->where('staff_role', VendorMember::ROLE_OWNER)
            ->first();
        if ($existing) {
            return $existing;
        }

        $userId = $vendor->user_id;
        if (! $userId) {
            throw new RuntimeException('Satıcı sahibi bulunamadı.');
        }

        return VendorMember::firstOrCreate(
            ['vendor_id' => $vendor->id, 'user_id' => $userId],
            ['staff_role' => VendorMember::ROLE_OWNER]
        );
    }

    public function roleFor(User $user, Vendor $vendor): ?string
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('vendor_members')) {
            return (int) $vendor->user_id === (int) $user->id ? VendorMember::ROLE_OWNER : null;
        }

        $this->ensureOwner($vendor);

        return VendorMember::query()
            ->where('vendor_id', $vendor->id)
            ->where('user_id', $user->id)
            ->value('staff_role');
    }

    public function canManageInventory(User $user, Vendor $vendor): bool
    {
        return $this->roleFor($user, $vendor) === VendorMember::ROLE_OWNER;
    }

    public function canOperate(User $user, Vendor $vendor): bool
    {
        return in_array($this->roleFor($user, $vendor), [VendorMember::ROLE_OWNER, VendorMember::ROLE_OPS], true);
    }

    public function canProof(User $user, OohOccupancy $occupancy): bool
    {
        $vendor = $occupancy->inventory?->vendor;
        if (! $vendor) {
            return false;
        }
        $role = $this->roleFor($user, $vendor);
        if (in_array($role, [VendorMember::ROLE_OWNER, VendorMember::ROLE_OPS], true)) {
            return true;
        }

        return $role === VendorMember::ROLE_FIELD
            && (int) $occupancy->assigned_user_id === (int) $user->id;
    }

    public function assertCanManageInventory(User $user, Vendor $vendor): void
    {
        if (! $this->canManageInventory($user, $vendor)) {
            abort(403, 'Envanter yalnızca işletme sahibi tarafından yönetilir.');
        }
    }

    public function assertCanOperate(User $user, Vendor $vendor): void
    {
        if (! $this->canOperate($user, $vendor)) {
            abort(403, 'Bu işlem için yetkiniz yok.');
        }
    }

    public function invite(Vendor $vendor, User $actor, string $email, string $name, string $staffRole, ?string $password = null): VendorMember
    {
        $this->assertCanManageInventory($actor, $vendor);
        if (! in_array($staffRole, [VendorMember::ROLE_OPS, VendorMember::ROLE_FIELD], true)) {
            throw new RuntimeException('Geçersiz ekip rolü.');
        }

        return DB::transaction(function () use ($vendor, $email, $name, $staffRole, $password) {
            $user = User::query()->where('email', $email)->first();
            if ($user) {
                if ($user->vendor_id && (int) $user->vendor_id !== (int) $vendor->id) {
                    throw new RuntimeException('Bu e-posta başka bir satıcıya bağlı.');
                }
                if ($user->role === 'customer' || $user->role === 'admin') {
                    throw new RuntimeException('Bu e-posta müşteri veya yönetici hesabına ait.');
                }
            } else {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password ?: Str::password(12)),
                    'role' => 'vendor',
                    'is_active' => true,
                ]);
            }

            $user->update([
                'vendor_id' => $vendor->id,
                'role' => 'vendor',
            ]);

            return VendorMember::updateOrCreate(
                ['vendor_id' => $vendor->id, 'user_id' => $user->id],
                ['staff_role' => $staffRole]
            );
        });
    }
}
