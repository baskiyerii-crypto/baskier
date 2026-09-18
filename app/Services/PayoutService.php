<?php

namespace App\Services;

use App\Models\PayoutRequest;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PayoutService
{
    public function __construct(private NotificationService $notifications) {}

    public function availableBalance(Vendor $vendor): float
    {
        $pending = (float) PayoutRequest::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', 'pending')
            ->sum('amount');

        return max(0, (float) $vendor->balance - $pending);
    }

    /**
     * @return list<int>
     */
    public function weekdays(): array
    {
        $raw = (string) Setting::get('payout_weekdays', '1,2,3,4,5');
        $days = array_values(array_filter(array_map('intval', explode(',', $raw)), fn ($d) => $d >= 1 && $d <= 7));

        return $days !== [] ? $days : [1, 2, 3, 4, 5];
    }

    public function minAmount(): float
    {
        return max(1, (float) Setting::get('payout_min_amount', 10));
    }

    public function autoAfterDays(): int
    {
        return max(1, (int) Setting::get('payout_auto_after_days', 14));
    }

    public function isPayoutDay(?\DateTimeInterface $when = null): bool
    {
        $iso = (int) ($when ? \Carbon\Carbon::parse($when)->isoWeekday() : now()->isoWeekday());

        return in_array($iso, $this->weekdays(), true);
    }

    /**
     * @return list<string>
     */
    public function weekdayLabels(): array
    {
        $map = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
        $labels = [];
        foreach ($this->weekdays() as $d) {
            $labels[] = $map[$d] ?? (string) $d;
        }

        return $labels;
    }

    public function rulesText(): string
    {
        $days = implode(', ', $this->weekdayLabels());
        $min = number_format($this->minAmount(), 2, ',', '.');
        $wait = Setting::commissionWaitDays();
        $auto = $this->autoAfterDays();
        $monthDay = Setting::payoutDayOfMonth();

        return "Hakediş, siparişin komisyon bekleme süresi ({$wait} gün) dolduktan sonra cüzdanınıza işlenir. "
            ."Para çekme talebi yalnızca şu günlerde açılabilir: {$days}. "
            ."Asgari çekim tutarı ₺{$min}'dir. "
            ."Ayın {$monthDay}. günü platform hakediş tarama günüdür. "
            ."Çekilebilir bakiyeniz {$auto} gün boyunca talep edilmezse sistem sizin adınıza çekim talebi oluşturur; tutar yönetici onayından sonra IBAN’ınıza havale edilir.";
    }

    public function request(Vendor $vendor, float $amount, string $iban, string $holder, string $source = 'manual'): PayoutRequest
    {
        $iban = strtoupper(preg_replace('/\s+/', '', $iban) ?? '');
        if (! preg_match('/^TR[0-9]{24}$/', $iban)) {
            throw new RuntimeException('IBAN numarası TR ile başlamalı ve 26 karakter olmalıdır.');
        }
        if ($source === 'manual' && ! $this->isPayoutDay()) {
            throw new RuntimeException('Bugün para çekme günü değil. İzin verilen günler: '.implode(', ', $this->weekdayLabels()).'.');
        }
        $min = $this->minAmount();
        if ($amount + 0.0001 < $min) {
            throw new RuntimeException('Asgari çekim tutarı ₺'.number_format($min, 2, ',', '.').' dir.');
        }

        return DB::transaction(function () use ($vendor, $amount, $iban, $holder, $source) {
            $locked = Vendor::query()->whereKey($vendor->id)->lockForUpdate()->firstOrFail();
            $available = $this->availableBalance($locked);
            if ($amount > $available + 0.0001) {
                throw new RuntimeException('Çekilebilir bakiye yetersiz. Net tutar: ₺'.number_format($available, 2, ',', '.'));
            }

            $row = PayoutRequest::create([
                'vendor_id' => $locked->id,
                'amount' => $amount,
                'iban' => $iban,
                'account_holder' => $holder,
                'status' => 'pending',
                'source' => $source,
                'requested_at' => now(),
                'admin_note' => 'IBAN: '.$iban.' | Hesap Sahibi: '.$holder,
            ]);

            $locked->forceFill([
                'payout_iban' => $iban,
                'payout_account_holder' => $holder,
            ])->save();

            $this->notifyAdmins($row, $locked);

            return $row;
        });
    }

    public function approve(PayoutRequest $request, ?string $adminNote = null): void
    {
        DB::transaction(function () use ($request, $adminNote) {
            /** @var PayoutRequest $locked */
            $locked = PayoutRequest::query()->whereKey($request->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'pending') {
                throw new RuntimeException('Bu talep zaten işlendi.');
            }
            $vendor = Vendor::query()->whereKey($locked->vendor_id)->lockForUpdate()->firstOrFail();
            if ((float) $vendor->balance < (float) $locked->amount) {
                throw new RuntimeException('Satıcı bakiyesi yetersiz.');
            }
            $vendor->decrement('balance', $locked->amount);
            $vendor->refresh();
            $vendor->balanceTransactions()->create([
                'amount' => -1 * (float) $locked->amount,
                'type' => 'payout',
                'reference_type' => 'payout_request',
                'reference_id' => $locked->id,
                'description' => 'Para çekme onayı',
                'balance_after' => $vendor->balance,
            ]);
            $note = $adminNote !== null && $adminNote !== ''
                ? $adminNote
                : $locked->admin_note;
            $locked->update([
                'status' => 'approved',
                'admin_note' => $note,
                'processed_at' => now(),
                'approved_at' => now(),
            ]);
        });
    }

    public function reject(PayoutRequest $request, ?string $adminNote = null): void
    {
        if ($request->status !== 'pending') {
            throw new RuntimeException('Bu talep zaten işlendi.');
        }
        $request->update([
            'status' => 'rejected',
            'admin_note' => $adminNote ?: $request->admin_note,
            'processed_at' => now(),
            'rejected_at' => now(),
        ]);
    }

    public function autoRequestForgotten(): int
    {
        $created = 0;
        $afterDays = $this->autoAfterDays();
        $min = $this->minAmount();
        Vendor::query()
            ->where('balance', '>=', $min)
            ->whereNotNull('payout_iban')
            ->orderBy('id')
            ->chunkById(50, function ($vendors) use (&$created, $afterDays, $min) {
                foreach ($vendors as $vendor) {
                    $hasPending = PayoutRequest::query()
                        ->where('vendor_id', $vendor->id)
                        ->where('status', 'pending')
                        ->exists();
                    if ($hasPending) {
                        continue;
                    }
                    $available = $this->availableBalance($vendor);
                    if ($available < $min) {
                        continue;
                    }
                    $lastCredit = $vendor->balanceTransactions()
                        ->whereIn('type', ['hakedis', 'topup'])
                        ->latest('id')
                        ->first();
                    $anchor = $lastCredit?->created_at ?? $vendor->updated_at;
                    if ($anchor && $anchor->gt(now()->subDays($afterDays))) {
                        continue;
                    }
                    try {
                        $this->request(
                            $vendor,
                            $available,
                            (string) $vendor->payout_iban,
                            (string) ($vendor->payout_account_holder ?: $vendor->name),
                            'auto'
                        );
                        $created++;
                    } catch (RuntimeException) {
                    }
                }
            });

        return $created;
    }

    private function notifyAdmins(PayoutRequest $row, Vendor $vendor): void
    {
        $url = route('admin.vendor-payout-requests.index', ['status' => 'pending']);
        $title = $row->source === 'auto' ? 'Otomatik para çekme talebi' : 'Yeni para çekme talebi';
        $body = $vendor->name.' ₺'.number_format((float) $row->amount, 2, ',', '.').' tutarında çekim talebi oluşturdu. İşlem tamamlanana kadar kuyrukta kalır.';
        User::query()->where('role', 'admin')->where('is_active', true)->get()->each(function (User $admin) use ($title, $body, $url, $row) {
            $this->notifications->notify($admin, $title, $body, [
                'type' => 'payout_request',
                'payout_request_id' => $row->id,
                'sticky' => true,
            ], $url);
        });
        if ($vendor->user) {
            $this->notifications->notify(
                $vendor->user,
                'Para çekme talebiniz alındı',
                'Talebiniz finans onayına iletildi. Onaylandıktan sonra belirttiğiniz IBAN’a havale yapılır.',
                ['type' => 'payout_request', 'payout_request_id' => $row->id],
                $vendor->prefersOutdoorPanel() && \Illuminate\Support\Facades\Route::has('outdoor-panel.payout-requests.index')
                    ? route('outdoor-panel.payout-requests.index')
                    : route('vendor.payout-requests.index')
            );
        }
    }
}
