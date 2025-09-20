<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    protected $fillable = [
        'name',
        'province',
    ];
    public $timestamps = false;

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function coopDetails()
    {
        return $this->hasMany(CoopDetail::class, 'municipality_id');
    }
}
