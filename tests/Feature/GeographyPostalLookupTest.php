<?php

namespace Tests\Feature;

use App\Models\TurkiyeIl;
use App\Models\TurkiyeIlce;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeographyPostalLookupTest extends TestCase
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
    }

    public function test_postal_lookup_returns_match(): void
    {
        $this->getJson('/api/v1/geography/postal-lookup?code=06100')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.matches.0.district_id', 1231)
            ->assertJsonPath('data.matches.0.province_id', 6);
    }

    public function test_postal_lookup_invalid_length(): void
    {
        $this->getJson('/api/v1/geography/postal-lookup?code=12')
            ->assertStatus(422);
    }

    public function test_postal_lookup_falls_back_to_province_districts_when_exact_code_missing(): void
    {
        $this->getJson('/api/v1/geography/postal-lookup?code=06420')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.fallback', true)
            ->assertJsonPath('data.matches.0.province_id', 6)
            ->assertJsonPath('data.matches.0.district_id', 1231);
    }
}
