<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Moa extends Model
{
    protected $fillable = [
        'coop_program_id',
        'file_path',
        'file_name',
        'date_signed',
        'uploaded_by',
    ];

    public function coopProgram()
    {
        return $this->belongsTo(CoopProgram::class);
    }
}
