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
                ->where('program_status', 'Finished')
                ->get();

            if ($coopPrograms->isEmpty()) {
                $this->info('No finished cooperative programs found.');
                return 0;
            }

            foreach ($coopPrograms as $coopProgram) {
                $schedules = $coopProgram->ammortizationSchedules->sortBy('due_date');
                if ($schedules->isEmpty())
                    continue;

                // Ensure all payments are fully paid
                $allPaid = $schedules->every(fn($s) => $s->status === 'Paid');
                if (!$allPaid)
                    continue;

                $coop = $coopProgram->cooperative;

                // Cooperative details
                $address = $coop->detail->address ?? 'Unknown Address';
                $contact = $coop->detail->contact_number ?? 'N/A';
                $chairman = optional($coop->members->firstWhere('position', 'Chairman'))->last_name ?? 'N/A';
                $treasurer = optional($coop->members->firstWhere('position', 'Treasurer'))->last_name ?? 'N/A';
                $manager = optional($coop->members->firstWhere('position', 'Manager'))->last_name ?? 'N/A';

                // ✅ Generate PDF directly from Blade view
                $pdf = Pdf::loadView('amortization_schedule', [
                    'coop' => $coop,
                    'coopProgram' => $coopProgram,
                    'schedules' => $schedules,
                    'address' => $address,
                    'contact' => $contact,
                    'chairman' => $chairman,
                    'treasurer' => $treasurer,
                    'manager' => $manager,
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
                $coopProgram->ammortizationSchedules()->delete();

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
