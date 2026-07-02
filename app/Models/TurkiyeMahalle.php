<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TurkiyeMahalle extends Model
{
    public $incrementing = false;

    protected $keyType = 'int';

    protected $table = 'turkiye_mahalleler';

    protected $fillable = ['id', 'district_id', 'province_id', 'name'];

    public function ilce(): BelongsTo
    {
        return $this->belongsTo(TurkiyeIlce::class, 'district_id', 'id');
    }

    public function il(): BelongsTo
    {
        return $this->belongsTo(TurkiyeIl::class, 'province_id', 'id');
    }
}
