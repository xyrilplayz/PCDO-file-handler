<?php

namespace App\Console\Commands;

use App\Models\Cooperative;
use Illuminate\Console\Command;
use App\Models\CoopProgram;
use League\Csv\Writer;
use carbon\Carbon;
use App\Models\Old;
class ExportCompletedLoans extends Command
{
    protected $signature = 'export:completed-loans'; // <-- this is the command name
    protected $description = 'Export fully paid cooperative loans to CSV';


    public function handle()
    {
        $this->info('Running export...');

        $coopPrograms = CoopProgram::with(['ammortizationSchedules', 'program', 'cooperative'])
            ->where('program_status', 'Finished')
            ->get();

        $csvData = [];

        foreach ($coopPrograms as $coopProgram) {
            $schedules = $coopProgram->ammortizationSchedules->sortBy('due_date');
            if ($schedules->isEmpty())
                continue;

            $allPaid = $schedules->every(fn($s) => $s->status === 'Paid');
            if (!$allPaid)
                continue;

            // Loan summary
            $csvData[] = ['field' => 'cooperative_name', 'value' => $coopProgram->cooperative->name ?? 'Unknown Cooperative'];
            $csvData[] = ['field' => 'program_name', 'value' => $coopProgram->program->name];
            $csvData[] = ['field' => 'loan_amount', 'value' => $coopProgram->loan_ammount];
            $csvData[] = ['field' => 'start_date', 'value' => $coopProgram->start_date->format('Y-m-d')];
            $csvData[] = ['field' => 'grace_period', 'value' => $coopProgram->with_grace];
            $csvData[] = ['field' => 'term_months', 'value' => $coopProgram->program->term_months];

            $csvData[] = []; // blank row

            // Schedule header
            $csvData[] = ['due_date', 'installment', 'date_paid', 'amount_paid', 'status'];

            foreach ($schedules as $schedule) {
                $csvData[] = [
                    $schedule->due_date->format('Y-m-d'),
                    $schedule->installment,
                    $schedule->date_paid ? $schedule->date_paid->format('Y-m-d') : '',
                    $schedule->amount_paid ?? '',
                    $schedule->status
                ];
            }

            $csvData[] = []; // optional blank row

            $coopProgram->exported = true;
            $coopProgram->save();
        }

        if (empty($csvData)) {
            $this->info('No completed loans to export.');
            return 0;
        }

        // ✅ Use in-memory CSV
        $csv = Writer::createFromString('');
        $csv->insertOne(array_keys($csvData[0]));
        $csv->insertAll($csvData);

        $csvContent = $csv->getContent(); // CSV content as string

        // Save CSV as binary in `old` table
        Old::create([
            'coop_program_id' => $coopProgram->id,
            'file_content' => $csvContent,
        ]);

        $this->info('✅ CSV exported and saved to the old table successfully!');
    }
}
