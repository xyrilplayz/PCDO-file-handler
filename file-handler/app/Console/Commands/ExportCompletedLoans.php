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
    protected $signature = 'export:completed-loans';
    protected $description = 'Export fully paid cooperative loans to CSV';

    public function handle()
    {
        $this->info('Running export...');

        $coopPrograms = CoopProgram::with([
            'ammortizationSchedules',
            'program',
            'cooperative.detail',
            'cooperative.members'
        ])
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

            $coop = $coopProgram->cooperative;

            // from coop_detail
            $address = $coop->detail->address ?? 'Unknown Address';
            $contact = $coop->detail->contact_number ?? 'N/A';

            // from coop_members
            $chairman = $coop->members->firstWhere('position', 'Chairman')->last_name ?? 'N/A';
            $treasurer = $coop->members->firstWhere('position', 'Treasurer')->last_name ?? 'N/A';
            $manager = $coop->members->firstWhere('position', 'Manager')->last_name ?? 'N/A';

            // Loan summary
            $csvData[] = ['Cooperative_name', $coop->name ?? 'Unknown Cooperative', 'Address', $address];
            $csvData[] = ['Program_name', $coopProgram->program->name, 'Coop Chairman', $chairman];
            $csvData[] = ['Loan_amount', $coopProgram->loan_ammount, 'Coop Treasurer', $treasurer];
            $csvData[] = ['Start_date', $coopProgram->start_date->format('Y-m-d'), 'Coop Manager', $manager];
            $csvData[] = ['Grace_period', $coopProgram->with_grace, 'Contact Number', $contact];
            $csvData[] = ['Term_months', $coopProgram->program->term_months, 'Project', $coopProgram->program->project ?? 'N/A'];
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
        $coopProgram->ammortizationSchedules()->delete();

        $this->info('✅ CSV exported and saved to the old table successfully!');
    }
}
