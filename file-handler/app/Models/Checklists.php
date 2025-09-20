<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checklists extends Model
{
    protected $fillable = ['name'];
    public $timestamps = false;

    function programs()
    {
        return $this->belongsToMany(Programs::class, 'program_checklists', 'checklist_id', 'program_id')->withPivot('id');
    }
    public function programChecklists()
    {
        return $this->hasMany(ProgramChecklists::class);
    }
    public function uploads()
    {
        return $this->hasMany(CoopProgramChecklist::class, 'program_checklist_id');
    }

}
