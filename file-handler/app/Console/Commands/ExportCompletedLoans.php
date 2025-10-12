<?php

namespace App\Console\Commands;

use App\Models\Cooperative;
use Illuminate\Console\Command;
use App\Models\CoopProgram;
use League\Csv\Writer;
use Carbon\Carbon;
use App\Models\Old;
use Barryvdh\DomPDF\Facade\Pdf;


class ExportCompletedLoans extends Command
{
    protected $signature = 'export:completed-loans';
    protected $description = 'Export fully paid cooperative loans to CSV';

    public function handle()
    {
        try {
            $this->info('🏁 Running export of completed cooperative loans...');

            // Load finished cooperative programs
            $coopPrograms = CoopProgram::with([
                'ammortizationSchedules',
                'program',
                'cooperative.details',
                'cooperative.members'
            ])
                ->whereIn('program_status', ['Finished', 'Resolved'])
                ->get();

            if ($coopPrograms->isEmpty()) {
                $this->info('No finished/resolved cooperative programs found.');
                return 0;
            }

            foreach ($coopPrograms as $coopProgram) {
                $schedules = $coopProgram->ammortizationSchedules->sortBy('due_date');
                if ($schedules->isEmpty())
                    continue;

                // Ensure all payments are fully paid
                $allPaid = $schedules->every(fn($s) => in_array($s->status, ['Paid', 'Resolved']));
                if (!$allPaid)
                    continue;


                $coop = $coopProgram->cooperative;

                //chairman
                $chairman = $coopProgram->cooperative->members
                    ->where('position', 'Chairman')
                    ->first();
                $chairmanFullName = $chairman
                    ? trim("{$chairman->first_name} {$chairman->middle_name} {$chairman->last_name}")
                    : 'N/A';

                //treasurer
                $treasurer = $coopProgram->cooperative->members
                    ->where('position', 'Treasurer')
                    ->first();
                $treasurerFullName = $treasurer
                    ? trim("{$treasurer->first_name} {$treasurer->middle_name} {$treasurer->last_name}")
                    : 'N/A';

                //manager
                $manager = $coopProgram->cooperative->members
                    ->where('position', 'Manager')
                    ->first();
                $managerFullName = $manager
                    ? trim("{$manager->first_name} {$manager->middle_name} {$manager->last_name}")
                    : 'N/A';

                // Cooperative details
                $address = $coop->detail->address ?? 'Unknown Address';
                $contact = $coop->detail->contact_number ?? 'N/A';

                // ✅ Generate PDF directly from Blade view
                $pdf = Pdf::loadView('amortization_schedule', [
                    'coop' => $coop,
                    'coopProgram' => $coopProgram,
                    'schedules' => $schedules,
                    'address' => $address,
                    'contact' => $contact,
                    'chairman' => $chairmanFullName,
                    'treasurer' => $treasurerFullName,
                    'manager' => $managerFullName,
                ])
                    ->setPaper('a4', 'portrait')
                    ->setOptions([
                        'dpi' => 80, // lower DPI = more fits on one page
                        'defaultFont' => 'sans-serif',
                        'isHtml5ParserEnabled' => true,
                        'isRemoteEnabled' => true,
                    ]);


                // ✅ Get the binary content (for BLOB)
                $pdfBinary = $pdf->output();

                // ✅ Save binary PDF directly into the `Old` table
                Old::create([
                    'coop_program_id' => $coopProgram->id,
                    'file_content' => $pdfBinary, // this is the BLOB column
                ]);

                // ✅ Mark program as exported
                $coopProgram->exported = true;
                $coopProgram->save();

                // ✅ Optionally clear schedules
                //$coopProgram->ammortizationSchedules()->delete();

                $this->info("✅ Exported PDF for {$coop->name} saved in database (BLOB)");
            }

            $this->info('🎉 All fully paid cooperative loans have been exported successfully!');
            return 0;

        } catch (\Exception $e) {
            \Log::error('ExportCompletedLoans failed: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            $this->error('❌ ExportCompletedLoans failed: ' . $e->getMessage());
            return 1;
        }
    }

}
