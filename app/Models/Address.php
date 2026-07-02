<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\File;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'turkiye_district_id',
        'turkiye_neighborhood_id',
        'label',
        'full_name',
        'phone',
        'city',
        'district',
        'neighborhood',
        'cadde',
        'sokak',
        'bina_no',
        'ic_kapi_no',
        'line1',
        'line2',
        'postal_code',
        'is_default',
        'is_billing_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_billing_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function turkiyeIlce(): BelongsTo
    {
        return $this->belongsTo(TurkiyeIlce::class, 'turkiye_district_id', 'id');
    }

    public function turkiyeMahalle(): BelongsTo
    {
        return $this->belongsTo(TurkiyeMahalle::class, 'turkiye_neighborhood_id', 'id');
    }

    /**
     * @return array<string, mixed>
     */
    public static function validationRules(Request $request): array
    {
        return [
            'label' => ['nullable', 'string', 'max:50'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'turkiye_district_id' => ['required', 'integer'],
            'turkiye_neighborhood_id' => ['required', 'integer'],
            'cadde' => ['nullable', 'string', 'max:120'],
            'sokak' => ['nullable', 'string', 'max:120'],
            'bina_no' => ['nullable', 'string', 'max:60'],
            'ic_kapi_no' => ['nullable', 'string', 'max:60'],
            'line1' => ['nullable', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'is_default' => ['boolean'],
            'is_billing_default' => ['boolean'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function withTurkiyeLocation(array $validated): array
    {
        $validated['cadde'] = trim((string) ($validated['cadde'] ?? '')) ?: null;
        $validated['sokak'] = trim((string) ($validated['sokak'] ?? '')) ?: null;
        $validated['bina_no'] = trim((string) ($validated['bina_no'] ?? '')) ?: null;
        $validated['ic_kapi_no'] = trim((string) ($validated['ic_kapi_no'] ?? '')) ?: null;
        // Some existing SQLite schemas keep line1/line2 as NOT NULL.
        // Store empty string instead of null to avoid integrity errors.
        $validated['line1'] = trim((string) ($validated['line1'] ?? ''));
        $validated['line2'] = trim((string) ($validated['line2'] ?? ''));

        $district = self::resolveDistrict((int) $validated['turkiye_district_id']);
        if (! $district) {
            throw ValidationException::withMessages([
                'turkiye_district_id' => 'Seçilen ilçe geçersiz.',
            ]);
        }
        $validated['city'] = $district['province_name'];
        $validated['district'] = $district['district_name'];
        $validated['postal_code'] = $district['postal_code'];

        $neighborhood = self::resolveNeighborhood((int) $validated['turkiye_neighborhood_id']);
        if (! $neighborhood) {
            throw ValidationException::withMessages([
                'turkiye_neighborhood_id' => 'Seçilen mahalle geçersiz.',
            ]);
        }
        if ((int) $neighborhood['district_id'] !== (int) $district['district_id']) {
            throw ValidationException::withMessages([
                'turkiye_neighborhood_id' => 'Seçilen mahalle bu ilçeye ait değil.',
            ]);
        }
        $validated['neighborhood'] = $neighborhood['name'];

        // Foreign keys are nullable; if source row comes from JSON fallback and is not
        // present in DB tables, keep textual location fields but store nullable FK columns.
        if (!($district['exists_in_db'] ?? false)) {
            $validated['turkiye_district_id'] = null;
        }
        if (!($neighborhood['exists_in_db'] ?? false)) {
            $validated['turkiye_neighborhood_id'] = null;
        }

        return $validated;
    }

    private static function resolveDistrict(int $districtId): ?array
    {
        $district = TurkiyeIlce::query()->with('il')->find($districtId);
        if ($district) {
            return [
                'district_id' => (int) $district->id,
                'district_name' => (string) $district->name,
                'province_name' => (string) ($district->il?->name ?? ''),
                'postal_code' => (string) ($district->postal_code ?? ''),
                'exists_in_db' => true,
            ];
        }

        $districts = self::readJson(database_path('data/districts.json'));
        $provinces = self::readJson(database_path('data/provinces.json'));
        if (! is_array($districts) || ! is_array($provinces)) {
            return null;
        }

        $row = collect($districts)->first(fn ($item) => (int) ($item['id'] ?? 0) === $districtId);
        if (! $row) {
            return null;
        }
        $provinceId = (int) ($row['provinceId'] ?? 0);
        $provinceName = '';
        $provinceRows = $provinces['data'] ?? [];
        if (is_array($provinceRows)) {
            $province = collect($provinceRows)->first(fn ($item) => (int) ($item['id'] ?? 0) === $provinceId);
            $provinceName = (string) ($province['name'] ?? '');
        }
        $postal = preg_replace('/\D/', '', (string) ($row['postalCode'] ?? ''));

        return [
            'district_id' => $districtId,
            'district_name' => (string) ($row['name'] ?? ''),
            'province_name' => $provinceName,
            'postal_code' => strlen($postal) === 5 ? $postal : '',
            'exists_in_db' => false,
        ];
    }

    private static function resolveNeighborhood(int $neighborhoodId): ?array
    {
        $neighborhood = TurkiyeMahalle::query()->find($neighborhoodId);
        if ($neighborhood) {
            return [
                'id' => (int) $neighborhood->id,
                'district_id' => (int) $neighborhood->district_id,
                'name' => (string) $neighborhood->name,
                'exists_in_db' => true,
            ];
        }

        $rows = self::readJson(database_path('data/neighborhoods.json'));
        if (! is_array($rows)) {
            return null;
        }
        $row = collect($rows)->first(fn ($item) => (int) ($item['id'] ?? 0) === $neighborhoodId);
        if (! $row) {
            return null;
        }

        return [
            'id' => $neighborhoodId,
            'district_id' => (int) ($row['districtId'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'exists_in_db' => false,
        ];
    }

    private static function readJson(string $path): mixed
    {
        if (! File::exists($path)) {
            return null;
        }

        return json_decode((string) File::get($path), true);
    }

    public function getFormattedAttribute(): string
    {
        $parts = array_filter([
            $this->neighborhood,
            $this->cadde,
            $this->sokak,
            $this->bina_no ? 'Bina No: '.$this->bina_no : null,
            $this->ic_kapi_no ? 'İç Kapı No: '.$this->ic_kapi_no : null,
            $this->line1 ? 'Adres Notu: '.$this->line1 : null,
            $this->line2,
            trim(($this->district ? $this->district.' / ' : '').$this->city),
            $this->postal_code,
        ]);

        return implode(', ', $parts);
    }

    public static function capturePlaceHint(array $validated): void
    {
        $districtId = isset($validated['turkiye_district_id']) ? (int) $validated['turkiye_district_id'] : 0;
        if ($districtId <= 0) {
            return;
        }

        $cadde = trim((string) ($validated['cadde'] ?? ''));
        $sokak = trim((string) ($validated['sokak'] ?? ''));
        if ($cadde === '' && $sokak === '') {
            return;
        }

        AddressPlaceHint::query()->updateOrCreate(
            [
                'district_id' => $districtId,
                'cadde' => $cadde !== '' ? $cadde : null,
                'sokak' => $sokak !== '' ? $sokak : null,
            ],
            ['last_used_at' => now()],
        );
    }
}
