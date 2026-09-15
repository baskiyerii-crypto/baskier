<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractVendorAcceptance;
use App\Models\Setting;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class ContractPublishService
{
    public function publishToVendors(Contract $contract): int
    {
        if (! in_array($contract->audience, ['all', 'vendor'], true) || ! $contract->is_active) {
            return 0;
        }

        $days = Setting::contractAcceptanceDays();
        $dueAt = now()->addDays(max(1, $days));
        $count = 0;

        Vendor::query()
            ->where(function ($q) {
                $q->where('is_active', true)->orWhereNotNull('contract_suspended_at');
            })
            ->chunkById(100, function ($vendors) use ($contract, $dueAt, &$count) {
                foreach ($vendors as $vendor) {
                    ContractVendorAcceptance::updateOrCreate(
                        [
                            'vendor_id' => $vendor->id,
                            'contract_id' => $contract->id,
                            'version' => $contract->version,
                        ],
                        [
                            'status' => 'pending',
                            'due_at' => $dueAt,
                            'accepted_at' => null,
                        ]
                    );
                    $count++;
                }
            });

        return $count;
    }

    public function accept(Vendor $vendor, ContractVendorAcceptance $acceptance): void
    {
        if ($acceptance->vendor_id !== $vendor->id) {
            abort(403);
        }

        DB::transaction(function () use ($vendor, $acceptance) {
            $acceptance->update([
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            $hasPendingOverdue = ContractVendorAcceptance::query()
                ->where('vendor_id', $vendor->id)
                ->where('status', 'pending')
                ->where('due_at', '<', now())
                ->exists();

            if (! $hasPendingOverdue && $vendor->contract_suspended_at) {
                $vendor->update([
                    'contract_suspended_at' => null,
                    'is_active' => true,
                ]);
            }
        });
    }

    public function suspendOverdue(): int
    {
        $count = 0;
        ContractVendorAcceptance::query()
            ->where('status', 'pending')
            ->where('due_at', '<', now())
            ->with('vendor')
            ->chunkById(100, function ($rows) use (&$count) {
                foreach ($rows as $row) {
                    $row->update(['status' => 'expired']);
                    if ($row->vendor) {
                        $row->vendor->update([
                            'contract_suspended_at' => now(),
                            'is_active' => false,
                        ]);
                        $count++;
                    }
                }
            });

        return $count;
    }
}
