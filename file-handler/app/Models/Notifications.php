<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    /** @use HasFactory<\Database\Factories\NotificationFactory> */
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'coop_id',
        'type',
        'subject',
        'body',
        'processed',
    ];

    public function schedule()
    {
        return $this->belongsTo(AmmortizationSchedule::class, 'schedule_id');
    }

    // link directly to cooperative
    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class, 'coop_id', 'id');
    }


}
