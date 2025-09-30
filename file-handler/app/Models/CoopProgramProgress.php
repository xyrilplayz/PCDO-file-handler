<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CoopProgramProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'coop_program_id',
        'title',
        'description',
        'file_name',
        'mime_type',
        'file_content',
    ];

    public function coopProgram()
    {
        return $this->belongsTo(CoopProgram::class);
    }
}
