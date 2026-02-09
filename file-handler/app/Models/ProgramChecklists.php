<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramChecklists extends Model
{
    // Link to uploads (CoopProgramChecklist)
    public function uploads()
    {
        return $this->hasMany(CoopProgramChecklist::class, 'program_checklist_id');
    }

    // Optional: link back to the Program (if needed)
    public function program()
    {
        return $this->belongsTo(Programs::class, 'program_id');
    }

    // Optional: link to the Checklist details
    public function checklist()
    {
        return $this->belongsTo(Checklists::class, 'checklist_id');
    }
}
