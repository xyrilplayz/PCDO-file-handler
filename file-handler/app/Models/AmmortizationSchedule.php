<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmmortizationSchedule extends Model
{
    protected $fillable = [
        'coop_program_id',
        'due_date',
        'installment',
        'status',
        'date_paid',
        'amount_paid',
        'penalty_amount',
        'balance',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'date_paid' => 'datetime',
    ];

    public function markPaid()
    {
        $this->status = true;
        $this->save();
    }

    /**
     * Relationship to CoopProgram
     */
    public function program()
    {
        return $this->belongsTo(\App\Models\Programs::class, 'program_id');
    }

    public function cooperative()
    {
        return $this->belongsTo(\App\Models\Cooperative::class, 'coop_id');
    }

    public function coopProgram()
    {
        return $this->belongsTo(\App\Models\CoopProgram::class, 'coop_program_id');
    }

    public function pendingnotifications()
    {
        return $this->hasOne(PendingNotification::class, 'schedule_id', 'id');
    }

    public function pendingNotification()
{
    return $this->hasMany(PendingNotification::class, 'schedule_id');
}

    /**
     * Automatically check if the program is finished when a schedule is updated
     */
    protected static function booted()
    {
        static::updated(function ($schedule) {
            // Only trigger if the status changed to 'Paid'
            if ($schedule->wasChanged('status') && $schedule->status === 'Paid') {
                $schedule->checkIfLastSchedulePaid();
            }
        });
    }

    /**
     * Check if this is the last schedule and update program status
     */
    public function checkIfLastSchedulePaid()
    {
        $coopProgram = $this->coopProgram;
        if (!$coopProgram)
            return;

        $lastSchedule = $coopProgram->ammortizationSchedules()
            ->orderByDesc('due_date')
            ->first();

        if ($lastSchedule && $lastSchedule->status === 'Paid' && $coopProgram->program_status !== 'Finished') {
            $coopProgram->program_status = 'Finished';
            $coopProgram->save();
        }
    }

}
