<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoopDetail extends Model
{
    protected $fillable = [
        'coop_id',
        'municipality',
        'asset_size',
        'coop_type',
        'status/category',
        'bond_of_membership',
        'area_of_operation',
        'citizenship',
        'members_count',
        'total_asset',
        'net_surplus',
    ];

    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class, 'coop_id');
    }
    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }
}
