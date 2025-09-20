<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cooperative;
use App\Models\CoopProgram;
use App\Models\Programs;
use App\Models\CoopProgramChecklist;

class CoopProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allPrograms = Programs::all();
        foreach (Cooperative::all() as $coop) {
            $program = $allPrograms->random();
            $coopProgram = CoopProgram::create([
                'coop_id' => (string)$coop->id,
                'program_id' => $program->id,
                'start_date' => now(),
                'end_date' => now()->addMonths($program->term_months),
                'program_status' => 'Ongoing',
                'number' => null,
                'email' => $coop->name . '@example.com',
                'loan_ammount' => rand($program->min_amount, $program->max_amount),
            ]);
            
            foreach ($program->checklists as $checklist) {
                CoopProgramChecklist::create([
                    'coop_program_id' => $coopProgram->id,
                    'program_checklist_id' => $checklist->pivot->id,
                    'file_name' => null,
                    'mime_type' => null,
                    'file_content' => null,
                ]);
            }
        }
    }
}
