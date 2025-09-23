<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoopProgram extends Model
{
    /** @use HasFactory<\Database\Factories\CoopProgramFactory> */
    use HasFactory;

    protected $fillable = [
        'coop_id',
        'program_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'program_status',
        'email',
        'loan_ammount',
        'with_grace'
    ];

    protected $casts = [
        'coop_id' => 'string',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function ammortizationSchedules()
    {
        return $this->hasMany(\App\Models\AmmortizationSchedule::class, 'coop_program_id');
    }
    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class, 'coop_id');

    }

    public function program()
    {
        return $this->belongsTo(Programs::class, 'program_id');
    }

    public function checklist()
    {
        return $this->hasMany(CoopProgramChecklist::class);
    }

    public function olds()
    {
        return $this->hasMany(Old::class);
    }

    public function detail()
    {
        return $this->hasOne(CoopDetail::class, 'coop_id');
    }

    public function members()
    {
        return $this->hasMany(CoopMember::class, 'coop_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); // adjust if your foreign key is different
    }


    function generateChecklists()
    {
        $items = $this->program->checklists;
        foreach ($items as $item) {
            CoopProgramChecklist::firstOrCreate([
                'coop_program_id' => $this->id,
                'checklist_id' => $item->id,
            ]);
        }
    }
}