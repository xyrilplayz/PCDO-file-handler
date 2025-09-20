<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $fillable = [
        'name',
    ];
    public $timestamps = false;

    public function municipalities()
    {
        return $this->hasMany(Municipality::class, 'province_id');
    }
}
