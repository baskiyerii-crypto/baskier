<?php

namespace App\Console\Commands;

use App\Models\OohOccupancy;
use App\Models\VendorMember;
use App\Services\NotificationService;
use App\Services\OutdoorOccupancyService;
use Illuminate\Console\Command;

class NotifyUpcomingOohJobs extends Command
{
    protected $signature = 'ooh:notify-upcoming';

    protected $description = 'Notify outdoor owners and staff one day before booked jobs';

    public function handle(NotificationService $notifications, OutdoorOccupancyService $occupancy): int
    {
        $occupancy->expireHolds();
        $target = now()->addDay()->toDateString();
        $jobs = OohOccupancy::query()
            ->with(['inventory.vendor.user', 'assignedUser'])
            ->where('kind', OohOccupancy::KIND_BOOKED)
            ->whereDate('starts_on', $target)
            ->get();

        $count = 0;
        foreach ($jobs as $job) {
            $vendor = $job->inventory?->vendor;
            if (! $vendor) {
                continue;
            }
            $userIds = VendorMember::query()
                ->where('vendor_id', $vendor->id)
                ->whereIn('staff_role', [VendorMember::ROLE_OWNER, VendorMember::ROLE_OPS])
                ->pluck('user_id');
            if ($job->assigned_user_id) {
                $userIds->push($job->assigned_user_id);
            }
            if ($vendor->user_id) {
                $userIds->push($vendor->user_id);
            }
            foreach ($userIds->unique()->filter() as $userId) {
                $user = \App\Models\User::query()->find($userId);
                if (! $user) {
                    continue;
                }
                $notifications->notify(
                    $user,
                    'Yarın asım işi var',
                    ($job->inventory?->title ?? 'Pano').' · '.$job->starts_on->toDateString(),
                    ['type' => 'ooh_job', 'occupancy_id' => $job->id],
                    route('outdoor-panel.jobs')
                );
                $count++;
            }
        }
        $this->info("Notified {$count} recipient(s) for jobs starting {$target}.");

        return self::SUCCESS;
    }
}
