<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CoopProgram;
use App\Models\CoopProgramChecklist;

class CleanupArchivedPrograms extends Command
{
    protected $signature = 'cleanup:coop-programs';
    protected $description = 'Delete finished, exported, and archived coop programs';

    public function handle()
    {
        $programs = CoopProgram::where('program_status', 'Finished')
            ->where('exported', 1)
            ->where('archived', 1)
            ->get();

        foreach ($programs as $program) {
            CoopProgramChecklist::where('coop_program_id', $program->id)->delete();
            $program->delete();
            $this->info("🗑️ Deleted Coop Program ID: {$program->id}");
        }
    }
}
