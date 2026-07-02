<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TurkiyeIlce extends Model
{
    public $incrementing = false;

    public $timestamps = true;

    protected $keyType = 'int';

    protected $table = 'turkiye_ilceler';

    protected $fillable = ['id', 'province_id', 'name', 'postal_code'];

    public function il(): BelongsTo
    {
        return $this->belongsTo(TurkiyeIl::class, 'province_id');
    }

    public function mahalleler(): HasMany
    {
        return $this->hasMany(TurkiyeMahalle::class, 'district_id', 'id');
    }
}
