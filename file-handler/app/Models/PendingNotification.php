<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingNotification extends Model
{
    protected $fillable = ['schedule_id', 'coop_id', 'type', 'processed'];
    public $timestamps = false;


    public function pendingnotifications()
    {
        return $this->hasOne(AmmortizationSchedule::class, 'schedule_id', 'id');
    }

    public function schedule()
    {
        return $this->belongsTo(AmmortizationSchedule::class, 'schedule_id');
    }
}