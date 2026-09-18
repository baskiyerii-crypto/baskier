<?php

namespace App\Services;

use App\Models\OohInventory;
use App\Models\OohInventoryGrant;
use App\Models\OohOccupancy;
use App\Models\OutdoorCrew;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorMember;
use App\Support\Phone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class OutdoorStaffService
{
    public function ensureOwner(Vendor $vendor): VendorMember
    {
        if (! Schema::hasTable('vendor_members')) {
            throw new RuntimeException('Açık hava ekip tablosu henüz kurulmadı.');
        }

        $existing = VendorMember::query()
            ->where('vendor_id', $vendor->id)
            ->where('staff_role', VendorMember::ROLE_OWNER)
            ->first();
        if ($existing) {
            $this->syncOwnerPhone($vendor);

            return $existing;
        }

        $userId = $vendor->user_id;
        if (! $userId) {
            throw new RuntimeException('Satıcı sahibi bulunamadı.');
        }

        $member = VendorMember::firstOrCreate(
            ['vendor_id' => $vendor->id, 'user_id' => $userId],
            ['staff_role' => VendorMember::ROLE_OWNER]
        );
        $this->syncOwnerPhone($vendor);

        return $member;
    }

    public function syncOwnerPhone(Vendor $vendor): void
    {
        $user = $vendor->user;
        if (! $user) {
            return;
        }
        $phone = Phone::normalize($vendor->phone);
        if (! $phone || $user->phone) {
            return;
        }
        $user->update(['phone' => $phone]);
    }

    public function isFieldOperator(User $user, ?Vendor $vendor = null): bool
    {
        $vendor = $vendor ?: $user->vendor;
        if (! $vendor) {
            return false;
        }
        $role = $this->roleFor($user, $vendor);

        return in_array($role, [VendorMember::ROLE_FIELD, VendorMember::ROLE_OPS], true);
    }

    public function isOutdoorOperator(User $user): bool
    {
        $vendor = $user->vendor;
        if (! $vendor) {
            return false;
        }

        return $vendor->hasOutdoorTrack() || $vendor->outdoor_role !== null;
    }

    public function roleFor(User $user, Vendor $vendor): ?string
    {
        if (! Schema::hasTable('vendor_members')) {
            return (int) $vendor->user_id === (int) $user->id ? VendorMember::ROLE_OWNER : null;
        }

        $this->ensureOwner($vendor);

        return VendorMember::query()
            ->where('vendor_id', $vendor->id)
            ->where('user_id', $user->id)
            ->value('staff_role');
    }

    public function isAccountOwner(User $user, Vendor $vendor): bool
    {
        return $this->roleFor($user, $vendor) === VendorMember::ROLE_OWNER;
    }

    public function hasActiveInventoryGrant(User $user, Vendor $vendor): bool
    {
        if (! Schema::hasTable('ooh_inventory_grants')) {
            return false;
        }

        $now = now();
        $personal = OohInventoryGrant::query()
            ->where('vendor_id', $vendor->id)
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->where('starts_at', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->exists();
        if ($personal) {
            return true;
        }

        $crewId = VendorMember::query()
            ->where('vendor_id', $vendor->id)
            ->where('user_id', $user->id)
            ->value('crew_id');
        if (! $crewId) {
            return false;
        }

        return OohInventoryGrant::query()
            ->where('vendor_id', $vendor->id)
            ->where('crew_id', $crewId)
            ->whereNull('revoked_at')
            ->where('starts_at', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->exists();
    }

    public function canManageInventory(User $user, Vendor $vendor): bool
    {
        return $this->isAccountOwner($user, $vendor) || $this->hasActiveInventoryGrant($user, $vendor);
    }

    public function canEditInventory(User $user, OohInventory $inventory): bool
    {
        $vendor = $inventory->vendor;
        if (! $vendor) {
            return false;
        }
        if ($this->isAccountOwner($user, $vendor)) {
            return true;
        }
        if (! $this->hasActiveInventoryGrant($user, $vendor)) {
            return false;
        }

        return (int) $inventory->created_by_user_id === (int) $user->id;
    }

    public function canOperate(User $user, Vendor $vendor): bool
    {
        return $this->isAccountOwner($user, $vendor);
    }

    public function canProof(User $user, OohOccupancy $occupancy): bool
    {
        $vendor = $occupancy->inventory?->vendor;
        if (! $vendor) {
            return false;
        }
        if ($this->isAccountOwner($user, $vendor)) {
            return true;
        }

        $role = $this->roleFor($user, $vendor);
        if (! $role) {
            return false;
        }

        return (int) $occupancy->assigned_user_id === (int) $user->id;
    }

    public function assertAccountOwner(User $user, Vendor $vendor): void
    {
        if (! $this->isAccountOwner($user, $vendor)) {
            abort(403, 'Bu işlem yalnızca mecra sahibi içindir.');
        }
    }

    public function assertCanManageInventory(User $user, Vendor $vendor): void
    {
        if (! $this->canManageInventory($user, $vendor)) {
            abort(403, 'Envanter ekleme yetkiniz yok veya süresi doldu.');
        }
    }

    public function assertCanEditInventory(User $user, OohInventory $inventory): void
    {
        if (! $this->canEditInventory($user, $inventory)) {
            abort(403, 'Bu panoyu düzenleme yetkiniz yok.');
        }
    }

    public function assertCanOperate(User $user, Vendor $vendor): void
    {
        if (! $this->canOperate($user, $vendor)) {
            abort(403, 'Bu işlem için yetkiniz yok.');
        }
    }

    public function createCrew(Vendor $vendor, User $actor, string $name): OutdoorCrew
    {
        $this->assertAccountOwner($actor, $vendor);
        $name = trim($name);
        if ($name === '') {
            throw new RuntimeException('Ekip adı gerekli.');
        }

        $exists = OutdoorCrew::query()->where('vendor_id', $vendor->id)->where('name', $name)->exists();
        if ($exists) {
            throw new RuntimeException('Bu isimde bir ekip zaten var.');
        }

        return OutdoorCrew::create([
            'vendor_id' => $vendor->id,
            'name' => $name,
        ]);
    }

    public function grantInventory(
        Vendor $vendor,
        User $actor,
        ?int $crewId,
        ?int $userId,
        \DateTimeInterface $startsAt,
        ?\DateTimeInterface $endsAt
    ): OohInventoryGrant {
        $this->assertAccountOwner($actor, $vendor);
        if (($crewId && $userId) || (! $crewId && ! $userId)) {
            throw new RuntimeException('Yetki ya bir ekibe ya da bir kişiye verilmeli.');
        }
        if ($crewId) {
            $crew = OutdoorCrew::query()->where('vendor_id', $vendor->id)->whereKey($crewId)->first();
            if (! $crew) {
                throw new RuntimeException('Ekip bu satıcıya ait değil.');
            }
        }
        if ($userId) {
            $member = VendorMember::query()->where('vendor_id', $vendor->id)->where('user_id', $userId)->first();
            if (! $member || $member->staff_role === VendorMember::ROLE_OWNER) {
                throw new RuntimeException('Yetki verilecek kişi ekipte olmalı.');
            }
        }
        if ($endsAt && \Carbon\Carbon::parse($endsAt)->lt($startsAt)) {
            throw new RuntimeException('Bitiş tarihi başlangıçtan önce olamaz.');
        }

        return OohInventoryGrant::create([
            'vendor_id' => $vendor->id,
            'crew_id' => $crewId,
            'user_id' => $userId,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'granted_by_user_id' => $actor->id,
        ]);
    }

    public function revokeGrant(Vendor $vendor, User $actor, OohInventoryGrant $grant): void
    {
        $this->assertAccountOwner($actor, $vendor);
        abort_unless((int) $grant->vendor_id === (int) $vendor->id, 403);
        if ($grant->revoked_at) {
            return;
        }
        $grant->update(['revoked_at' => now()]);
    }

    public function invite(
        Vendor $vendor,
        User $actor,
        string $email,
        string $name,
        string $staffRole = VendorMember::ROLE_FIELD,
        ?string $password = null,
        ?int $crewId = null,
        ?string $phone = null,
    ): VendorMember {
        $this->assertAccountOwner($actor, $vendor);
        $staffRole = VendorMember::ROLE_FIELD;
        $email = mb_strtolower(trim($email));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Geçerli bir e-posta girin.');
        }
        if (! $password || strlen($password) < 8) {
            throw new RuntimeException('Şifre en az 8 karakter olmalı.');
        }
        $normalizedPhone = $phone ? Phone::normalize($phone) : null;
        if ($phone && ! $normalizedPhone) {
            throw new RuntimeException('Geçerli bir telefon numarası girin.');
        }
        if ($crewId) {
            $ok = OutdoorCrew::query()->where('vendor_id', $vendor->id)->whereKey($crewId)->exists();
            if (! $ok) {
                throw new RuntimeException('Seçilen ekip bulunamadı.');
            }
        }

        return DB::transaction(function () use ($vendor, $email, $name, $staffRole, $password, $crewId, $normalizedPhone) {
            $user = User::query()->where('email', $email)->first();
            if ($user) {
                if ($user->vendor_id && (int) $user->vendor_id !== (int) $vendor->id) {
                    throw new RuntimeException('Bu e-posta başka bir satıcıya bağlı.');
                }
                if ($user->role === 'customer' || $user->role === 'admin') {
                    throw new RuntimeException('Bu e-posta müşteri veya yönetici hesabına ait.');
                }
                $payload = [
                    'name' => $name ?: $user->name,
                    'vendor_id' => $vendor->id,
                    'role' => 'vendor',
                    'password' => $password,
                ];
                if ($normalizedPhone) {
                    $payload['phone'] = $normalizedPhone;
                }
                $user->update($payload);
            } else {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $normalizedPhone,
                    'password' => $password,
                    'role' => 'vendor',
                    'vendor_id' => $vendor->id,
                    'is_active' => true,
                ]);
            }

            $payload = ['staff_role' => $staffRole];
            if (Schema::hasColumn('vendor_members', 'crew_id')) {
                $payload['crew_id'] = $crewId;
            }

            return VendorMember::updateOrCreate(
                ['vendor_id' => $vendor->id, 'user_id' => $user->id],
                $payload
            );
        });
    }
}
