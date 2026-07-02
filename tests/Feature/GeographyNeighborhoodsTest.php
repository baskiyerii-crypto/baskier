<?php

namespace Tests\Feature;

use App\Models\TurkiyeIl;
use App\Models\TurkiyeIlce;
use App\Models\TurkiyeMahalle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeographyNeighborhoodsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        TurkiyeIl::query()->create(['id' => 6, 'name' => 'Ankara']);
        TurkiyeIlce::query()->create([
            'id' => 1231,
            'province_id' => 6,
            'name' => 'Çankaya',
            'postal_code' => '06100',
        ]);
        TurkiyeMahalle::query()->create([
            'id' => 500001,
            'district_id' => 1231,
            'province_id' => 6,
            'name' => 'Kızılay',
        ]);
    }

    public function test_neighborhoods_list_for_district(): void
    {
        $this->getJson('/api/v1/geography/districts/1231/neighborhoods')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.id', 500001)
            ->assertJsonPath('data.0.name', 'Kızılay');
    }
}
