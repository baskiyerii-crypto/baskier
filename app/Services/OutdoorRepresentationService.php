<?php

namespace App\Services;

use App\Models\OohInventory;
use App\Models\OohRepresentation;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class OutdoorRepresentationService
{
    public function tablesReady(): bool
    {
        return Schema::hasTable('ooh_representations');
    }

    /**
     * @return list<int>
     */
    public function sellerVendorIdsForOwner(int $ownerVendorId): array
    {
        if (! $this->tablesReady()) {
            return [$ownerVendorId];
        }

        $active = OohRepresentation::query()
            ->where('owner_vendor_id', $ownerVendorId)
            ->where('status', OohRepresentation::STATUS_ACTIVE)
            ->get();

        $exclusive = $active->firstWhere('exclusive', true);
        if ($exclusive) {
            return [(int) $exclusive->agency_vendor_id];
        }

        $ids = [$ownerVendorId];
        foreach ($active as $row) {
            $ids[] = (int) $row->agency_vendor_id;
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return list<int>
     */
    public function sellerVendorIdsForInventory(OohInventory $inventory): array
    {
        return $this->sellerVendorIdsForOwner((int) $inventory->vendor_id);
    }

    /**
     * @return list<int>
     */
    public function representedOwnerIds(Vendor $agency): array
    {
        if (! $this->tablesReady() || ! $agency->isOutdoorAgency()) {
            return [];
        }

        return OohRepresentation::query()
            ->where('agency_vendor_id', $agency->id)
            ->where('status', OohRepresentation::STATUS_ACTIVE)
            ->pluck('owner_vendor_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function canQuoteInventory(Vendor $seller, OohInventory $inventory): bool
    {
        return in_array((int) $seller->id, $this->sellerVendorIdsForInventory($inventory), true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<OohInventory>
     */
    public function representedInventoriesQuery(Vendor $agency)
    {
        $ownerIds = $this->representedOwnerIds($agency);

        return OohInventory::query()
            ->with(['images', 'vendor', 'occupancies'])
            ->where('status', OohInventory::STATUS_PUBLISHED)
            ->whereIn('vendor_id', $ownerIds ?: [0]);
    }

    public function invite(Vendor $from, string $counterpartyEmail, bool $exclusive = false, ?string $notes = null): OohRepresentation
    {
        $user = User::query()->where('email', $counterpartyEmail)->first();
        $other = $user?->vendor;
        if (! $other) {
            throw new RuntimeException('Bu e-posta ile açık hava hesabı bulunamadı.');
        }

        return $this->inviteByVendorId($from, (int) $other->id, $exclusive, $notes);
    }

    public function inviteByVendorId(Vendor $from, int $otherVendorId, bool $exclusive = false, ?string $notes = null): OohRepresentation
    {
        if (! $this->tablesReady()) {
            throw new RuntimeException('Temsil tablosu henüz kurulmadı.');
        }

        $from->refresh();
        $other = Vendor::query()->find($otherVendorId);
        if (! $other || ! $other->hasOutdoorTrack()) {
            throw new RuntimeException('Karşı açık hava hesabı bulunamadı.');
        }
        if ((int) $other->id === (int) $from->id) {
            throw new RuntimeException('Kendinizi temsilci olarak ekleyemezsiniz.');
        }

        $fromIsOwner = $from->isOutdoorOwner();
        $fromIsAgency = $from->isOutdoorAgency();
        if (! $fromIsOwner && ! $fromIsAgency) {
            throw new RuntimeException('Yalnızca mecra sahibi veya ajans temsil daveti gönderebilir.');
        }

        if ($fromIsOwner) {
            if (! $other->isOutdoorAgency()) {
                throw new RuntimeException('Davet edilen hesap ajans olmalı.');
            }
            $owner = $from;
            $agency = $other;
        } else {
            if (! $other->isOutdoorOwner()) {
                throw new RuntimeException('Davet edilen hesap mecra sahibi olmalı.');
            }
            $owner = $other;
            $agency = $from;
        }

        if ($exclusive) {
            $existingExclusive = OohRepresentation::query()
                ->where('owner_vendor_id', $owner->id)
                ->where('status', OohRepresentation::STATUS_ACTIVE)
                ->where('exclusive', true)
                ->where('agency_vendor_id', '!=', $agency->id)
                ->exists();
            if ($existingExclusive) {
                throw new RuntimeException('Bu sahip için münhasır ajans zaten tanımlı.');
            }
        }

        $row = OohRepresentation::query()->firstOrNew([
            'owner_vendor_id' => $owner->id,
            'agency_vendor_id' => $agency->id,
        ]);
        if ($row->exists && $row->status === OohRepresentation::STATUS_ACTIVE) {
            throw new RuntimeException('Bu bağ zaten aktif.');
        }

        $row->fill([
            'status' => OohRepresentation::STATUS_PENDING,
            'exclusive' => $exclusive,
            'notes' => $notes,
            'invited_by_vendor_id' => $from->id,
            'accepted_at' => null,
            'revoked_at' => null,
        ]);
        $row->save();
        $fresh = $row->fresh(['owner', 'agency']);
        $this->notifyInvite($fresh, $from, $other);

        return $fresh;
    }

    private function notifyInvite(OohRepresentation $row, Vendor $from, Vendor $other): void
    {
        if (! $other->user) {
            return;
        }
        app(NotificationService::class)->notify(
            $other->user,
            'Açık hava temsil daveti',
            $from->name.' sizinle bağ kurmak istiyor. İletişim bilgisi paylaşılmadan onaylayabilirsiniz.',
            ['type' => 'ooh_representation', 'representation_id' => $row->id],
            route('outdoor-panel.representations.index')
        );
    }

    public function accept(OohRepresentation $representation, Vendor $actor): OohRepresentation
    {
        if (! $representation->isPending()) {
            throw new RuntimeException('Bu davet onaylanamaz.');
        }

        $isCounterparty = (int) $actor->id === (int) $representation->owner_vendor_id
            || (int) $actor->id === (int) $representation->agency_vendor_id;
        if (! $isCounterparty) {
            throw new RuntimeException('Bu daveti onaylama yetkiniz yok.');
        }
        if ((int) $actor->id === (int) $representation->invited_by_vendor_id) {
            throw new RuntimeException('Daveti karşı taraf onaylamalı.');
        }

        if ($representation->exclusive) {
            $conflict = OohRepresentation::query()
                ->where('owner_vendor_id', $representation->owner_vendor_id)
                ->where('status', OohRepresentation::STATUS_ACTIVE)
                ->where('exclusive', true)
                ->whereKeyNot($representation->id)
                ->exists();
            if ($conflict) {
                throw new RuntimeException('Bu sahip için münhasır ajans zaten tanımlı.');
            }
        }

        $representation->update([
            'status' => OohRepresentation::STATUS_ACTIVE,
            'accepted_at' => now(),
            'revoked_at' => null,
        ]);

        $fresh = $representation->fresh(['owner.user', 'agency.user']);
        $inviter = Vendor::query()->find($fresh->invited_by_vendor_id);
        if ($inviter?->user) {
            app(NotificationService::class)->notify(
                $inviter->user,
                'Temsil daveti onaylandı',
                'Açık hava bağınız aktif. Mecralar artık paylaşılıyor.',
                ['type' => 'ooh_representation', 'representation_id' => $fresh->id],
                route('outdoor-panel.representations.index')
            );
        }

        return $fresh;
    }

    public function revoke(OohRepresentation $representation, Vendor $actor): void
    {
        $isParty = (int) $actor->id === (int) $representation->owner_vendor_id
            || (int) $actor->id === (int) $representation->agency_vendor_id;
        if (! $isParty) {
            throw new RuntimeException('Bu bağı iptal etme yetkiniz yok.');
        }
        if ($representation->status === OohRepresentation::STATUS_REVOKED) {
            return;
        }

        $representation->update([
            'status' => OohRepresentation::STATUS_REVOKED,
            'revoked_at' => now(),
        ]);
    }
}
