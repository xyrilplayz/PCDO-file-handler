<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'term_months',
        'grace_period',
        'min_amount',
        'max_amount',
    ];

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function cooperatives()
    {
        return $this->hasMany(Cooperative::class);
    }
}
