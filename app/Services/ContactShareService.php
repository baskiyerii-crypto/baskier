<?php

namespace App\Services;

use App\Models\ContactShare;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBalanceTransaction;
use Illuminate\Support\Facades\DB;

class ContactShareService
{
    public function shareAfterAccept(
        User $customer,
        Vendor $vendor,
        string $contextType,
        ?int $contextId,
        bool $customerConsent,
        bool $vendorConsent,
        bool $chargeTabelaFee = false
    ): ContactShare {
        if (! $customerConsent || ! $vendorConsent) {
            throw new \InvalidArgumentException(__('panel.consent_required'));
        }

        return DB::transaction(function () use ($customer, $vendor, $contextType, $contextId, $customerConsent, $vendorConsent, $chargeTabelaFee) {
            if ($chargeTabelaFee) {
                $fee = Setting::tabelaMeetingFee();
                if ((float) $vendor->balance < $fee) {
                    throw new \RuntimeException(__('panel.insufficient_balance_for_tabela'));
                }
                $vendor->decrement('balance', $fee);
                VendorBalanceTransaction::create([
                    'vendor_id' => $vendor->id,
                    'type' => 'tabela_meeting_fee',
                    'amount' => -$fee,
                    'reference_type' => $contextType,
                    'reference_id' => $contextId,
                    'description' => 'Tabela birim teklif / görüşme ücreti',
                ]);
            }

            return ContactShare::create([
                'customer_user_id' => $customer->id,
                'vendor_id' => $vendor->id,
                'context_type' => $contextType,
                'context_id' => $contextId,
                'customer_consented' => true,
                'vendor_consented' => true,
                'shared_at' => now(),
            ]);
        });
    }
}
