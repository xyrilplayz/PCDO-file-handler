<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Old extends Model
{
    protected $fillable = [
        'coop_program_id',
        'file_content',
        
    ];
    protected $table = 'old'; 

        public function coopProgram()
    {
        return $this->belongsTo(CoopProgram::class);
    }

}
