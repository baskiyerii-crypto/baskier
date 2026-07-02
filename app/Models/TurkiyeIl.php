<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TurkiyeIl extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $table = 'turkiye_iller';

    protected $fillable = ['id', 'name'];

    public function ilceler(): HasMany
    {
        return $this->hasMany(TurkiyeIlce::class, 'province_id');
    }
}
