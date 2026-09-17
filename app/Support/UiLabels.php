<?php

namespace App\Support;

final class UiLabels
{
    public static function orderStatuses(): array
    {
        return [
            'pending' => __('panel.status_pending'),
            'paid' => __('panel.status_paid'),
            'confirmed' => __('panel.status_confirmed'),
            'design_review' => __('panel.status_design_review'),
            'in_production' => __('panel.status_in_production'),
            'ready_to_ship' => __('panel.status_ready_to_ship'),
            'shipped' => __('panel.status_shipped'),
            'delivered' => __('panel.status_delivered'),
            'completed' => __('panel.status_completed'),
            'cancelled' => __('panel.status_cancelled'),
            'disputed' => __('panel.status_disputed'),
        ];
    }

    public static function orderStatus(?string $status): string
    {
        return self::orderStatuses()[$status] ?? ($status ?: '—');
    }

    public static function designApprovalStatuses(): array
    {
        return [
            'pending' => __('panel.status_pending'),
            'approved' => __('panel.status_approved'),
            'revision_requested' => __('panel.status_revision'),
        ];
    }

    public static function designApprovalStatus(?string $status): string
    {
        return self::designApprovalStatuses()[$status] ?? ($status ?: '—');
    }

    public static function supportTicketStatuses(): array
    {
        return [
            'open' => __('panel.status_open'),
            'pending' => __('panel.status_pending'),
            'closed' => __('panel.status_closed'),
        ];
    }

    public static function supportTicketStatus(?string $status): string
    {
        return self::supportTicketStatuses()[$status] ?? ($status ?: '—');
    }

    public static function payoutRequestStatuses(): array
    {
        return [
            'pending' => __('panel.status_pending'),
            'approved' => __('panel.status_approved'),
            'rejected' => __('panel.status_rejected'),
        ];
    }

    public static function payoutRequestStatus(?string $status): string
    {
        return self::payoutRequestStatuses()[$status] ?? ($status ?: '—');
    }

    public static function channels(): array
    {
        return [
            'physical_quote' => __('panel.channel_physical'),
            'freelancer' => __('panel.channel_freelancer'),
            'tabela' => __('panel.channel_tabela'),
        ];
    }

    public static function channel(?string $channel): string
    {
        return self::channels()[$channel] ?? ($channel ?: '—');
    }

    public static function documentTypes(): array
    {
        return [
            'tax_plate' => __('panel.doc_tax_plate'),
            'company_registration' => __('panel.doc_company_registration'),
            'certificate' => __('panel.doc_certificate'),
            'diploma' => __('panel.doc_diploma'),
            'portfolio_accreditation' => __('panel.doc_portfolio_accreditation'),
            'course' => __('panel.doc_course'),
            'other' => __('panel.doc_other'),
        ];
    }

    public static function documentType(?string $type): string
    {
        return self::documentTypes()[$type] ?? ($type ?: '—');
    }

    public static function trustLevels(): array
    {
        return \App\Domain\TrustLevel::labels();
    }

    public static function trustLevel(?int $level): string
    {
        return \App\Domain\TrustLevel::label($level ?? 0);
    }

    public static function verificationStatuses(): array
    {
        return [
            'pending' => __('panel.status_pending'),
            'verified' => __('panel.status_verified'),
            'approved' => __('panel.status_approved'),
            'rejected' => __('panel.status_rejected'),
        ];
    }

    public static function verificationStatus(?string $status): string
    {
        return self::verificationStatuses()[$status] ?? ($status ?: '—');
    }

    public static function genericStatuses(): array
    {
        return [
            'pending' => __('panel.status_pending'),
            'approved' => __('panel.status_approved'),
            'rejected' => __('panel.status_rejected'),
            'open' => __('panel.status_open'),
            'closed' => __('panel.status_closed'),
            'expired' => __('panel.status_expired'),
            'accepted' => __('panel.status_accepted'),
            'draft' => __('panel.draft'),
            'published' => __('panel.published'),
        ];
    }

    public static function status(?string $status): string
    {
        return self::genericStatuses()[$status] ?? ($status ?: '—');
    }

    public static function freelancerTiers(): array
    {
        return [
            'standard' => __('panel.tier_standard'),
            'medium' => __('panel.tier_medium'),
            'professional' => __('panel.tier_professional'),
        ];
    }

    public static function freelancerTier(?string $tier): string
    {
        return self::freelancerTiers()[$tier] ?? ($tier ?: '—');
    }

    public static function tracks(): array
    {
        return [
            'physical_products' => __('panel.track_physical_products'),
            'physical_quote' => __('panel.track_physical_quote'),
            'freelancer' => __('panel.track_freelancer'),
        ];
    }

    public static function carrierTrackingUrl(?string $carrier, ?string $trackingNumber): ?string
    {
        if (! $carrier || ! $trackingNumber) {
            return null;
        }
        $c = mb_strtolower($carrier);
        $t = urlencode(trim($trackingNumber));
        if (str_contains($c, 'yurtiçi') || str_contains($c, 'yurtici')) {
            return "https://www.yurticikargo.com/tr/online-servisler/gonderi-sorgula?code={$t}";
        }
        if (str_contains($c, 'aras')) {
            return "https://www.araskargo.com.tr/kargo-takip/{$t}";
        }
        if (str_contains($c, 'mng')) {
            return "https://www.mngkargo.com.tr/gonderitakip/{$t}";
        }
        if (str_contains($c, 'sürat') || str_contains($c, 'surat')) {
            return "https://www.suratkargo.com.tr/KargoTakip/?kargotakipno={$t}";
        }
        if (str_contains($c, 'ptt')) {
            return "https://gonderitakip.ptt.gov.tr/Track/Verify?q={$t}";
        }

        return null;
    }
}
