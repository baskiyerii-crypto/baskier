<?php

namespace App\Services;

use App\Models\OohOccupancy;
use App\Models\OohProof;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class OutdoorProofService
{
    public function __construct(private OutdoorStaffService $staff) {}

    public function submit(OohOccupancy $occupancy, User $actor, UploadedFile $photo, float $lat, float $lng): OohProof
    {
        if ($occupancy->kind !== OohOccupancy::KIND_BOOKED) {
            throw new RuntimeException('Kanıt yalnızca rezerve işler için yüklenir.');
        }
        if (! $this->staff->canProof($actor, $occupancy)) {
            abort(403, 'Bu iş size atanmadı.');
        }

        $occupancy->loadMissing('inventory');
        $inventory = $occupancy->inventory;
        $distance = (int) round($this->haversineMeters(
            (float) $inventory->lat,
            (float) $inventory->lng,
            $lat,
            $lng
        ));
        $radius = (int) ($inventory->proof_radius_m ?: 75);
        $path = $photo->store('ooh/proofs/'.$occupancy->id, 'public');

        return OohProof::create([
            'ooh_occupancy_id' => $occupancy->id,
            'user_id' => $actor->id,
            'photo_path' => $path,
            'lat' => $lat,
            'lng' => $lng,
            'distance_m' => $distance,
            'is_valid' => $distance <= $radius,
            'captured_at' => now(),
        ]);
    }

    public function haversineMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $earth * asin(min(1, sqrt($a)));
    }
}
