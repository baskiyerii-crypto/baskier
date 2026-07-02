<?php

namespace App\Support;

final class UiLabels
{
    /**
     * @return array<string, string>
     */
    public static function orderStatuses(): array
    {
        return [
            'pending' => 'Beklemede',
            'confirmed' => 'Onaylandı',
            'design_review' => 'Tasarım inceleme',
            'in_production' => 'Üretimde',
            'ready_to_ship' => 'Kargoya hazır',
            'shipped' => 'Kargoda',
            'delivered' => 'Teslim edildi',
            'completed' => 'Tamamlandı',
            'cancelled' => 'İptal',
            'disputed' => 'Uyuşmazlık',
        ];
    }

    public static function orderStatus(?string $status): string
    {
        if ($status === null || $status === '') {
            return '—';
        }

        return self::orderStatuses()[$status] ?? $status;
    }

    /**
     * @return array<string, string>
     */
    public static function designApprovalStatuses(): array
    {
        return [
            'pending' => 'Onay bekliyor',
            'approved' => 'Onaylandı',
            'revision_requested' => 'Revizyon istendi',
        ];
    }

    public static function designApprovalStatus(?string $status): string
    {
        if ($status === null || $status === '') {
            return '—';
        }

        return self::designApprovalStatuses()[$status] ?? $status;
    }

    /**
     * @return array<string, string>
     */
    public static function supportTicketStatuses(): array
    {
        return [
            'open' => 'Açık',
            'pending' => 'Beklemede',
            'closed' => 'Kapatıldı',
        ];
    }

    public static function supportTicketStatus(?string $status): string
    {
        if ($status === null || $status === '') {
            return '—';
        }

        return self::supportTicketStatuses()[$status] ?? $status;
    }

    /**
     * @return array<string, string>
     */
    public static function payoutRequestStatuses(): array
    {
        return [
            'pending' => 'Bekliyor',
            'approved' => 'Onaylandı',
            'rejected' => 'Reddedildi',
        ];
    }

    public static function payoutRequestStatus(?string $status): string
    {
        if ($status === null || $status === '') {
            return '—';
        }

        return self::payoutRequestStatuses()[$status] ?? $status;
    }
}
