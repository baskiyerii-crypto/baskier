<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Vendor;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class RemindExpiringSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:remind';

    protected $description = 'Aktif modül bitişine 7 gün ve 1 gün kala kurumsal hatırlatma gönderir.';

    public function handle(NotificationService $notifications): int
    {
        $sent = 0;
        $windows = [7, 1];
        $modules = [
            'freelancer' => ['label' => 'Freelancer', 'field' => 'freelancer_expires_at'],
            'quotes' => ['label' => 'Teklif verme', 'field' => 'quotes_expires_at'],
            'tabela' => ['label' => 'Tabela', 'field' => 'tabela_expires_at'],
            'ozalit' => ['label' => 'Ozalit', 'field' => 'ozalit_expires_at'],
            'outdoor' => ['label' => 'Açık hava', 'field' => 'outdoor_expires_at'],
        ];

        Vendor::query()->with('user')->orderBy('id')->chunkById(100, function ($vendors) use ($windows, $modules, $notifications, &$sent) {
            foreach ($vendors as $vendor) {
                $user = $vendor->user;
                if (! $user instanceof User) {
                    continue;
                }
                foreach ($modules as $key => $meta) {
                    $expires = $vendor->{$meta['field']};
                    if (! $expires || ! $expires->isFuture()) {
                        continue;
                    }
                    foreach ($windows as $days) {
                        $target = now()->addDays($days)->startOfDay();
                        if (! $expires->copy()->startOfDay()->equalTo($target)) {
                            continue;
                        }
                        $already = $user->notifications()
                            ->get()
                            ->contains(function ($n) use ($key, $days) {
                                $meta = $n->data['meta'] ?? [];

                                return ($meta['type'] ?? null) === 'subscription_reminder'
                                    && ($meta['module'] ?? null) === $key
                                    && (int) ($meta['days'] ?? 0) === $days;
                            });
                        if ($already) {
                            continue;
                        }
                        $cacheKey = 'sub-remind:'.$vendor->id.':'.$key.':'.$days.':'.$expires->toDateString();
                        if (! Cache::add($cacheKey, 1, now()->addDays(3))) {
                            continue;
                        }
                        $url = $this->renewUrl($vendor, $key);
                        $title = $meta['label'].' modülü yenileme hatırlatması';
                        $when = $days === 1 ? 'yarın' : '7 gün içinde';
                        $body = 'Sayın '.$vendor->name.', '.$meta['label'].' aboneliğinizin süresi '.$when.' ('.$expires->format('d.m.Y H:i').') dolacaktır. '
                            .'Kesinti yaşamamak için bakiyenizi kontrol ederek Modüller ekranından yenilemenizi rica ederiz.';
                        $notifications->notify($user, $title, $body, [
                            'type' => 'subscription_reminder',
                            'module' => $key,
                            'days' => $days,
                        ], $url);
                        $sent++;
                    }
                }
            }
        });

        $this->info("Gönderilen hatırlatma: {$sent}");

        return self::SUCCESS;
    }

    private function renewUrl(Vendor $vendor, string $module): string
    {
        if ($module === 'outdoor' && $vendor->prefersOutdoorPanel() && Route::has('outdoor-panel.subscriptions.index')) {
            return route('outdoor-panel.subscriptions.index');
        }

        return route('vendor.subscriptions.index');
    }
}
