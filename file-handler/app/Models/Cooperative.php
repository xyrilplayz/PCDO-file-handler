<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cooperative extends Model
{
    /** @use HasFactory<\Database\Factories\CooperativeFactory> */
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'holder',
        'type', // 'primary', 'secondary', 'tertiary'
    ];

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    public function parent()
    {
        return $this->belongsTo(Cooperative::class, 'holder');
    }
    public function coopProgram()
    {
        return $this->hasMany(CoopProgram::class, 'coop_id', 'id');
    }

    public function child()
    {
        return $this->hasMany(Cooperative::class, 'holder');
    }

    public function details()
    {
        return $this->hasOne(CoopDetail::class, 'coop_id', 'id');
    }

    public function members()
    {
        return $this->hasMany(CoopMember::class, 'coop_id', 'id');
    }

    public function programs()
    {
        return $this->hasMany(CoopProgram::class, 'coop_id', 'id');
    }
    public function coopDetail()
    {
        return $this->hasOne(CoopDetail::class, 'coop_id', 'id');
    }


    public function isValidHierarchy()
    {
        if ($this->type === 'primary') {
            return $this->holder === null;
        }

        if ($this->type === 'secondary') {
            if (!$this->holder)
                return false;
            $parent = $this->parent ?? Cooperative::find($this->holder);
            return $parent && $parent->type === 'primary';
        }

        if ($this->type === 'tertiary') {
            if (!$this->holder)
                return false;
            $parent = $this->parent ?? Cooperative::find($this->holder);
            return $parent && $parent->type === 'secondary';
        }

        return true;
    }
    public function oldPrograms()
    {
        return $this->hasManyThrough(
            Old::class,           // Final model
            CoopProgram::class,   // Intermediate model
            'coop_id',            // Foreign key on coop_programs table
            'coop_program_id',    // Foreign key on olds table
            'id',                 // Local key on cooperatives table
            'id'                  // Local key on coop_programs table
        )->whereHas('coopProgram', function ($q) {
            $q->whereIn('program_status', ['Finished', 'Resolved']);
        });
    }


    protected static function boot()
    {
        parent::boot();

        static::saving(function ($cooperative) {
            if (!$cooperative->isValidHierarchy()) {
                throw new \Exception('Invalid cooperative hierarchy.');
            }
        });
    }
    public function progressReports()
    {
        return $this->hasManyThrough(
            CoopProgramProgress::class,
            CoopProgram::class,
            'coop_id',        // Foreign key on coop_programs
            'coop_program_id', // Foreign key on coop_program_progress
            'id',             // Local key on cooperatives
            'id'              // Local key on coop_programs
        );
    }
}
