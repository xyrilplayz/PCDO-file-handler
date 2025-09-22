<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programs extends Model
{
    protected $fillable = [
        'name', 
        'description',
        'details',
        'term_months',
        'grace_period',
        'min_amount',
        'max_amount'
    ];
    public $timestamps = false;

    function checklists()
    {
        return $this->belongsToMany(Checklists::class, 'program_checklists', 'program_id', 'checklist_id')->withPivot('id');
    }

     public function coopProgram()
    {
        return $this->hasMany(CoopProgram::class, 'program_id');
    }

}
