<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $primaryKey = 'code';

    protected $fillable = ['code', 'name_en', 'name_tr'];

    public function localizedName(): string
    {
        return app()->getLocale() === 'en' ? $this->name_en : $this->name_tr;
    }

    /**
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function catalog()
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('countries')) {
            return collect(\App\Support\IsoCountries::all())
                ->map(fn ($names, $code) => new self([
                    'code' => $code,
                    'name_en' => $names[0],
                    'name_tr' => $names[1],
                ]))
                ->sortBy(fn (self $c) => $c->localizedName(), SORT_NATURAL | SORT_FLAG_CASE)
                ->values();
        }

        $localeCol = app()->getLocale() === 'en' ? 'name_en' : 'name_tr';

        return static::query()->orderBy($localeCol)->get();
    }
}
