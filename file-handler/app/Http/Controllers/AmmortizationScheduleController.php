<?php

namespace App\Http\Controllers;

use App\Models\PendingNotification;
use App\Models\resolved;
use Illuminate\Http\Request;
use App\Models\Programs;
use App\Models\ProgramChecklists;
use App\Models\CoopProgramChecklist;
use App\Models\AmmortizationSchedule;
use App\Models\CoopProgram;
use App\Models\Checklists;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LoanOverdueNotification;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class AmmortizationScheduleController extends Controller
{

    public function generateSchedule($id)
    {
        // Find the CoopProgram with its related program
        $coopProgram = CoopProgram::with('program')->findOrFail($id);

        // 🔍 1. Prevent duplicate schedule generation
        if ($coopProgram->ammortizationSchedules()->exists()) {
            return redirect()->route('loan.tracker.show', $coopProgram->id)
                ->with('error', 'Ammortization schedule already exists for this cooperative.');
        }

        // 🔍 2. Determine required uploads based on program name
        $programName = strtolower($coopProgram->program->name); // normalize case
        $requiredUploads = 0;

        if (in_array($programName, ['usad', 'sulong', 'copse'])) {
            $requiredUploads = 24;
        } elseif (in_array($programName, ['pclrp', 'licap'])) {
            $requiredUploads = 26;
        } else {
            return redirect()->route('loan.tracker.show', $coopProgram->id)
                ->with('error', 'Unknown program type. Cannot generate schedule.');
        }

        // 🔍 3. Count completed checklist uploads for this coop program
        $completedUploads = CoopProgramChecklist::where('coop_program_id', $coopProgram->id)
            ->whereNotNull('file_content') // adjust if your file column is named differently
            ->count();

        if ($completedUploads > $requiredUploads) {
            return redirect()->route('loan.tracker.show', $coopProgram->id)
                ->with('error', "Cannot generate ammortization schedule. 
                Required uploads: $requiredUploads, but only $completedUploads completed.");
        }

        // 🔍 4. Validate grace period vs term
        $monthsToPay = $coopProgram->program->term_months - $coopProgram->with_grace;

        if ($monthsToPay <= 0) {
            throw new \Exception('Invalid term and grace period.');
        }

        // 🔍 5. Compute installment
        $amountPerMonth = intdiv($coopProgram->loan_ammount, $monthsToPay);
        $remainder = $coopProgram->loan_ammount % $monthsToPay;
        $startDate = now()->addMonths($coopProgram->with_grace);

        // 🔍 6. Create amortization schedule
        for ($i = 1; $i <= $monthsToPay; $i++) {
            $amountDue = $amountPerMonth;


            if ($i === $monthsToPay) {
                $amountDue += $remainder;
            }

            AmmortizationSchedule::create([
                'coop_program_id' => $coopProgram->id,
                'due_date' => $startDate->copy()->addMonths($i - 1),
                'installment' => $amountDue,
                'status' => 'Unpaid', // ✅ ensure default
            ]);
        }


        return redirect()->route('loan.tracker.show', $coopProgram->id)
            ->with('success', 'Ammortization schedule generated successfully!');
    }



    public function show($coopProgramId)
    {

        $coop = CoopProgram::findOrFail($coopProgramId);
        $loan = CoopProgram::with(['program', 'cooperative', 'ammortizationSchedules'])
            ->findOrFail($coopProgramId);

        return view('loan_tracker', compact('loan', 'coop'));
    }

    public function markPaid(AmmortizationSchedule $schedule)
    {
        $schedule->update([
            'status' => 'Paid',
            'date_paid' => now(),
        ]);

        return back()->with('success', 'Payment marked as paid.');
    }

    public function sendOverdueEmail($scheduleId)
    {
        $schedule = AmmortizationSchedule::with('coopProgram', 'pendingnotifications')->findOrFail($scheduleId);
        $coopProgram = $schedule->coopProgram; // must be a CoopProgram instance
        $programEmail = $coopProgram->email ?? null;

        if ($programEmail) {
            Notification::route('mail', $programEmail)
                ->notify(new LoanOverdueNotification($schedule));


            return back()->with('success', 'Overdue email sent to ' . $programEmail);
        }

        return back()->with('error', 'No email found for this cooperative program.');
    }

    public function penalty(Request $request, AmmortizationSchedule $schedule)
    {
        if ($request->has('add')) {
            // 1% penalty of this schedule's amount due
            $penalty = $schedule->installment * 0.01;
            $schedule->penalty_amount += $penalty;
            $schedule->save();

            return back()->with('success', '1% penalty added to this overdue schedule.');
        }

        if ($request->has('remove')) {
            $schedule->penalty_amount = 0;
            $schedule->save();

            return back()->with('success', 'Penalty removed from this schedule.');
        }

        return back()->with('error', 'Invalid penalty action.');
    }

    public function notePayment(Request $request, $id)
    {
        $schedule = AmmortizationSchedule::findOrFail($id);

        $request->validate([
            'amount_paid' => 'required|numeric|min:0',
        ]);

        $payment = $request->amount_paid;
        $remaining = $payment;

        // Get all schedules for this loan/program ordered by due date
        $schedules = AmmortizationSchedule::where('coop_program_id', $schedule->coop_program_id)
            ->orderBy('due_date', 'asc')
            ->get();

        foreach ($schedules as $sch) {
            if ($remaining <= 0)
                break;

            // Total due for this schedule (installment + penalty + any balance carried over)
            $due = ($sch->installment + $sch->penalty_amount);

            // How much is still unpaid
            $needed = $due - $sch->amount_paid;

            if ($needed > 0) {
                $toPay = min($remaining, $needed);
                $sch->amount_paid += $toPay;
                $sch->balance = $due - $sch->amount_paid;
                $remaining -= $toPay;

                if ($sch->balance <= 0) {
                    $sch->status = 'Paid';
                    $sch->balance = 0;
                    $sch->date_paid = now();
                } else {
                    $sch->status = 'Partial Paid';
                }

                $sch->save();
            } else {
                // Already fully paid earlier
                $sch->status = 'Paid';
                $sch->balance = 0;
                $sch->date_paid = $sch->date_paid ?? now();
                $sch->save();
            }
        }

        return back()->with('success', 'Payment noted successfully.');
    }

    public function downloadPdf($coopProgramId)
    {
        // Load CoopProgram with relations
        $coopProgram = CoopProgram::with([
            'cooperative.details',
            'program',
            'ammortizationSchedules',
            'cooperative.members'
        ])->findOrFail($coopProgramId);

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


        $schedules = $coopProgram->ammortizationSchedules;
        // Load PDF view
        $pdf = PDF::loadView('amortization_schedule', [
            'coopProgram' => $coopProgram,
            'coop' => $coop,
            'schedules' => $schedules,
            'chairman' => $chairmanFullName ?? 'N/A',
            'treasurer' => $treasurerFullName ?? 'N/A',
            'manager' => $managerFullName ?? 'N/A',
            'contact' => $coopProgram->number ?? 'N/A',
        ])->setPaper([0, 0, 612, 1008], 'portrait'); // long bond paper 8.5x13

        $filename = ($coop->name ?? 'Cooperative').'_'.(($coopProgram->start_date)->format('Y-m-d') ?? 'Cooperative').'_Amortization.pdf';
        return $pdf->download($filename);
    }

    public function markIncomplete($id)
    {
        $coopProgram = CoopProgram::findOrFail($id);
        $coopProgram->program_status = 'Incomplete';
        $coopProgram->save();

        return redirect()->back()->with('success', 'Program marked as Incomplete.');
    }

    public function markResolved(Request $request, $loanId)
    {
        $loan = CoopProgram::with('ammortizationSchedules')->findOrFail($loanId);

        $path = null;
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
        }

        // Save record in resolved table
        Resolved::create([
            'loan_id' => $loan->id,
            'receipt_path' => $path,
        ]);

        // Mark all schedules as paid
        foreach ($loan->ammortizationSchedules as $schedule) {
            $schedule->update([
                'is_paid' => true,
                'status' => 'Paid',
                'date_paid' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Loan marked as resolved and all schedules set to Paid.');
    }




}
