<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delinquent extends Model
{
    protected $fillable = [
        'coop_program_id',
        'ammortization_schedule_id',
        'due_date',
        'date_paid',
        'status',
    ];

    public function coopProgram()
    {
        return $this->belongsTo(CoopProgram::class);
    }

    public function ammortization()
    {
        return $this->belongsTo(AmmortizationSchedule::class, 'ammortization_schedule_id');
    }
}
