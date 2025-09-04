<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cooperative extends Model
{
    use HasFactory;

    // Add fields you want to be mass assignable
    protected $fillable = [
        'name',
        'program_id',
        'user_id',
        'with_grace',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
