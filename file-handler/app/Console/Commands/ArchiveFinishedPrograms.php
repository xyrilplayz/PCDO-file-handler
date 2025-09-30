<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\CoopProgram;
use App\Models\CoopProgramChecklist;

class ArchiveFinishedPrograms extends Command
{
    protected $signature = 'archive:coop-programs';
    protected $description = 'Archive finished and exported cooperative program checklists/documents only';

    public function handle()
    {
        try {
            DB::transaction(function () {

                $programs = CoopProgram::where('program_status', 'Finished')
                    ->where('exported', 1)
                    ->where('archived', 0)
                    ->get();

                foreach ($programs as $program) {
                    $this->info("Archiving Coop Program ID: {$program->id}");

                    $checklists = CoopProgramChecklist::where('coop_program_id', $program->id)->get();

                    foreach ($checklists as $checklist) {
                        DB::table('finished_coop_program_checklist')->insert([
                            'coop_program_id' => $program->id,
                            'checklist_id' => $checklist->program_checklist_id,
                            'file_name' => $checklist->file_name,
                            'mime_type' => $checklist->mime_type,
                            'file_content' => $checklist->file_content,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $checklist->delete();
                    }

                    $program->archived = 1;
                    $program->save();

                    $this->info("✔ Program {$program->id} archived.");
                }
            });

            $this->info("✅ Archiving process completed successfully.");

        } catch (\Throwable $e) {
            $this->error("❌ Error: " . $e->getMessage());
            \Log::error("Archive command failed", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

}
