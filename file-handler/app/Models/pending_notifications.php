<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pending_notifications extends Model
{
    protected $fillable = ['schedule_id', 'coop_id', 'type', 'processed'];
    public $timestamps = false;

    public function schedule()
    {
        return $this->belongsTo(PaymentSchedule::class, 'schedule_id');
    }
}
