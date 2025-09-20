<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramChecklists extends Model
{
    public function uploads()
{
    return $this->hasMany(CoopProgramChecklist::class, 'program_checklist_id');
}

}
