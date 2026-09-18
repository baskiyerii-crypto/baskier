<?php

namespace Database\Seeders;

use App\Models\OohKgmTraffic;
use App\Models\TurkiyeIl;
use App\Models\TurkiyeIlce;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class OutdoorLocationDataSeeder extends Seeder
{
    public function run(): void
    {
        if (Schema::hasTable('turkiye_iller') && Schema::hasColumn('turkiye_iller', 'population')) {
            foreach ($this->provincePopulations2023() as $id => $population) {
                TurkiyeIl::query()->whereKey($id)->update([
                    'population' => $population,
                    'population_year' => 2023,
                ]);
            }
        }

        if (Schema::hasTable('turkiye_ilceler') && Schema::hasColumn('turkiye_ilceler', 'population')) {
            $kadikoy = TurkiyeIlce::query()->where('name', 'Kadıköy')->where('province_id', 34)->first();
            if ($kadikoy) {
                $kadikoy->update(['population' => 467919, 'population_year' => 2023]);
            }
        }

        if (Schema::hasTable('ooh_kgm_traffic')) {
            $rows = [
                ['road_ref' => 'D100', 'name' => 'D100 İstanbul Anadolu', 'aadt' => 85421, 'year' => 2023, 'lat' => 41.0000000, 'lng' => 29.0000000],
                ['road_ref' => 'O-4', 'name' => 'O-4 TEM', 'aadt' => 121300, 'year' => 2023, 'lat' => 40.9000000, 'lng' => 29.3000000],
                ['road_ref' => 'D200', 'name' => 'D200 Eskişehir yolu', 'aadt' => 41200, 'year' => 2023, 'lat' => 39.9200000, 'lng' => 32.7500000],
            ];
            foreach ($rows as $row) {
                OohKgmTraffic::query()->updateOrCreate(
                    ['road_ref' => $row['road_ref'], 'lat' => $row['lat'], 'lng' => $row['lng']],
                    $row
                );
            }
        }
    }

    /**
     * TÜİK ADNKS 2023 il nüfusları.
     *
     * @return array<int, int>
     */
    private function provincePopulations2023(): array
    {
        return [
            1 => 2274106, 2 => 111836, 3 => 755450, 4 => 524044, 5 => 335331, 6 => 5808156,
            7 => 2696249, 8 => 169341, 9 => 1148484, 10 => 1273616, 11 => 225165, 12 => 349296,
            13 => 350994, 14 => 320824, 15 => 273077, 16 => 3214571, 17 => 559383, 18 => 192633,
            19 => 524028, 20 => 1059440, 21 => 1813275, 22 => 408811, 23 => 588088, 24 => 237169,
            25 => 749754, 26 => 915418, 27 => 2154051, 28 => 450862, 29 => 169224, 30 => 353548,
            31 => 1704194, 32 => 445325, 33 => 1938389, 34 => 15655924, 35 => 4479473, 36 => 557076,
            37 => 388990, 38 => 1441523, 39 => 388655, 40 => 241467, 41 => 2079072, 42 => 2291165,
            43 => 577941, 44 => 806156, 45 => 1467423, 46 => 1171298, 47 => 870374, 48 => 1066802,
            49 => 405252, 50 => 305549, 51 => 371011, 52 => 770711, 53 => 346332, 54 => 1100115,
            55 => 1371274, 56 => 347385, 57 => 220437, 58 => 634924, 59 => 1142451, 60 => 606549,
            61 => 822270, 62 => 107271, 63 => 2143020, 64 => 373474, 65 => 1127613, 66 => 420699,
            67 => 609837, 68 => 432447, 69 => 83645, 70 => 266620, 71 => 192389, 72 => 349396,
            73 => 559891, 74 => 203595, 75 => 83832, 76 => 203595, 77 => 291116, 78 => 250287,
            79 => 147919, 80 => 559405, 81 => 405046,
        ];
    }
}
