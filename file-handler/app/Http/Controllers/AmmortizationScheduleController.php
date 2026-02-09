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
use Illuminate\Support\Facades\Mail;

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
    public function notifyOverdue()
    {
        // Get all schedules that are overdue and not paid/resolved
        $overdueSchedules = AmmortizationSchedule::whereNotIn('status', ['Paid', 'Resolved'])
            ->where('due_date', '<', now())
            ->get();

        // Group schedules by coop_program
        $groupedByCoop = $overdueSchedules->groupBy('coop_program_id');
 
        $report = [];

        foreach ($groupedByCoop as $coopProgramId => $schedules) {
            $coopProgram = CoopProgram::find($coopProgramId);
            $Email = $coopProgram->cooperative->coopDetail->email;

            if (!$Email) {
                $report[] = "CoopProgram ID {$coopProgramId} not found. Skipped.";
                continue;
            }

            $coopName = $coopProgram->cooperative->name ?? 'Cooperative';

            // Collect emails to notify
            $Email = $coopProgram->cooperative->coopDetail->email;

            if (empty($Email)) {
                $report[] = "No emails found for {$coopName}. Skipped.";
                continue;
            }

            // Build email content
            $scheduleList = $schedules->map(function ($s) {
                return "- Due: " . $s->due_date->format('M d, Y') .
                    " | Amount: ₱" . number_format($s->installment + ($s->balance ?? 0));
            })->implode("\n");

            $message = "Dear {$coopName},\n\nThe following schedules are overdue:\n{$scheduleList}\n\nPlease settle immediately.\n\nThanks.";

            try {
                // Send email
                Mail::raw($message, function ($mail) use ($Email) {
                    $mail->to($Email)
                        ->subject('Overdue Payment Notification');
                });

                $report[] = "Notification sent to {$coopName} ({implode(', ', $Email)}).";

            } catch (\Exception $e) {
                $report[] = "Failed to send to {$coopName}: " . $e->getMessage();
            }
        }

        // Log the report
        $reportText = "[" . now() . "] Overdue Notification Report:\n" . implode("\n", $report) . "\n";
        Log::channel('single')->info($reportText);

        return back()->with('success', 'Overdue notifications processed. Check log for details.');
    }

    public function OneTap(Request $request, $coopProgramId)
    {
        $request->validate([
            'receipt_image' => 'required|image|mimes:jpeg,png,jpg|max:5012',
        ]);

        $binaryImage = file_get_contents($request->file('receipt_image')->getRealPath());

        // Load CoopProgram with schedules
        $coopProgram = CoopProgram::with('ammortizationSchedules')->findOrFail($coopProgramId);


        foreach ($coopProgram->ammortizationSchedules as $schedule) {
            if (!in_array($schedule->status, ['Paid', 'Resolved'])) {
                $schedule->amount_paid = $schedule->installment + ($schedule->balance ?? 0);
                $schedule->balance = 0;
                $schedule->status = 'Paid';
                $schedule->date_paid = now();
                $schedule->receipt_image = $binaryImage;
                $schedule->save();
            }
        }

        return back()->with('success', 'All schedules marked as paid successfully.');
    }



    public function markPaid(Request $request, AmmortizationSchedule $schedule)
    {
        $request->validate([
            'receipt_image' => 'required|image|mimes:jpeg,png,jpg|max:5012',
        ]);

        $binaryImage = file_get_contents($request->file('receipt_image')->getRealPath());


        $remaining = ($schedule->balance ?? $schedule->installment) + $schedule->penalty_amount;


        $schedules = AmmortizationSchedule::where('coop_program_id', $schedule->coop_program_id)
            ->orderBy('due_date', 'asc')
            ->get();

        foreach ($schedules as $sch) {

            if ($remaining <= 0) {
                break;
            }

            if (in_array($sch->status, ['Paid', 'Resolved'])) {
                continue;
            }

            $due = ($sch->balance ?? $sch->installment) + $sch->penalty_amount;
            $balance = $due - $sch->amount_paid;

            if ($balance <= 0) {
                continue;
            }

            $toPay = min($remaining, $balance);

            $sch->amount_paid += $toPay;
            $newBalance = $balance - $toPay;

            if ($newBalance <= 0) {
                $sch->balance = null;
                $sch->status = 'Paid';
                $sch->date_paid = now();
            } else {
                $sch->balance = $newBalance;
                $sch->status = 'Partial Paid';
            }

            $sch->receipt_image = $binaryImage;
            $sch->save();

            $remaining -= $toPay;
        }

        $lastSchedule = AmmortizationSchedule::where('coop_program_id', $schedule->coop_program_id)
            ->orderByDesc('due_date')
            ->first();

        if ($lastSchedule && $lastSchedule->status === 'Partial Paid' && $lastSchedule->balance > 0) {

            AmmortizationSchedule::create([
                'coop_program_id' => $schedule->coop_program_id,
                'due_date' => $lastSchedule->due_date->copy()->addMonthsNoOverflow(1),
                'installment' => $lastSchedule->balance,
                'penalty_amount' => 0,
                'amount_paid' => 0,
                'balance' => $lastSchedule->balance,
                'status' => 'Unpaid',
            ]);

            // Clear balance from previous last schedule
            $lastSchedule->balance = null;
            $lastSchedule->save();
        }

        return back()->with('success', 'Payment marked as paid successfully.');
    }



    public function sendOverdueEmail($scheduleId)
    {
        $schedule = AmmortizationSchedule::with('coopProgram', 'pendingnotifications','cooperative')->findOrFail($scheduleId);
        $coopProgram = $schedule->coopProgram; // must be a CoopProgram instance
        $programEmail = $coopProgram->cooperative->coopDetail->email?? null;

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
        // Find the schedule being paid
        $schedule = AmmortizationSchedule::findOrFail($id);

        // Validate input
        $request->validate([
            'amount_paid' => 'required|numeric|min:0',
            'receipt_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $binaryImage = file_get_contents($request->file('receipt_image')->getRealPath());
        $remaining = $request->amount_paid;

        // Get schedules starting from the selected one
        $schedules = AmmortizationSchedule::where('coop_program_id', $schedule->coop_program_id)
            ->where('id', '>=', $schedule->id)
            ->orderBy('due_date')
            ->get();

        foreach ($schedules as $index => $sch) {

            if ($remaining <= 0)
                break;

            // Calculate effective due considering any previous balance
            $effectiveDue = $sch->installment + ($sch->balance ?? 0);

            $toPay = min($remaining, $effectiveDue);

            $sch->amount_paid += $toPay;

            $newBalance = $effectiveDue - $toPay;

            $sch->balance = $newBalance > 0 ? $newBalance : null;
            $sch->status = $newBalance > 0 ? 'Partial Paid' : 'Paid';
            $sch->date_paid = now();
            $sch->receipt_image = $binaryImage;
            $sch->save();

            $remaining -= $toPay;

            // ✅ Update next schedule's installment with leftover balance
            $nextSchedule = AmmortizationSchedule::where('coop_program_id', $sch->coop_program_id)
                ->where('id', '>', $sch->id)
                ->orderBy('id', 'asc')
                ->first();

            if ($nextSchedule && $newBalance > 0) {
                $nextSchedule->installment += $newBalance;
                $nextSchedule->save();
            }
        }

        $lastSchedule = AmmortizationSchedule::where('coop_program_id', $schedule->coop_program_id)
            ->orderByDesc('due_date')
            ->first();

        if ($lastSchedule && $lastSchedule->balance > 0) {
            $nextDueDate = Carbon::parse($lastSchedule->due_date)->addMonth();
            $carryOver = $lastSchedule->balance;



            $newSchedule = AmmortizationSchedule::create([
                'coop_program_id' => $lastSchedule->coop_program_id,
                'installment' => $carryOver,
                'amount_paid' => 0,
                'balance' => 0,
                'penalty_amount' => 0,
                'status' => 'Unpaid',
                'due_date' => $nextDueDate,
            ]);


            $lastSchedule->save();

            $lastSchedule = $newSchedule;
        }

        return back()->with('success', 'Payment noted successfully.');
    }


    // // 1️⃣ Apply payment to current/future schedules first
    // foreach ($schedules as $cur) {

    //     if ($remaining <= 0)
    //         break;

    //     // MISMONG ITO ANG MAGIGIING EQUATION
    //     $balance = $cur->installment - $cur->amount_paid;

    //     $cur [2] + $balance;

    //     if ($balance <= 0)
    //         continue; // skip fully paid schedules

    //     $toPay = min($remaining, $balance);

    //     $cur->amount_paid += $toPay;
    //     $cur->balance = $balance - $toPay;
    //     $cur->status = $cur->balance > 0 ? 'Partial Paid' : 'Paid';

    //     if ($cur->balance <= 0) {
    //         $cur->balance = 0;
    //         $cur->date_paid = now();
    //     }

    //     $cur->receipt_image = $binaryImage;
    //     $cur->save();

    //     $remaining -= $toPay;

    //     // 1️⃣a Calculate the next schedule relative to this one
    //     $nextSchedule = AmmortizationSchedule::where('coop_program_id', $schedule->coop_program_id)
    //         ->where('id', '>', $cur->id) // use $cur->id, not $schedule->id
    //         ->orderBy('id')
    //         ->first();

    //     if ($nextSchedule) {

    //         AmmortizationSchedule::where('coop_program_id', $schedule->coop_program_id)
    //         ->where('id',  '>', $schedule->id)
    //         ->update(['status' => 'Unpaid']);

    //         // Mark the next schedule as Unpaid
    //         $nextSchedule->status = 'Partial Paid';
    //         $nextSchedule->save();
    //     }
    // }

    // // 2️⃣ Apply remaining payment to previous schedules if any
    // if ($remaining > 0) {
    //     $previousSchedules = AmmortizationSchedule::where('coop_program_id', $schedule->coop_program_id)
    //         ->where('due_date', '<', $schedule->due_date)
    //         ->whereNotIn('status', ['Paid', 'Resolved'])
    //         ->orderBy('due_date')
    //         ->get();

    //     foreach ($previousSchedules as $sch) {
    //         if ($remaining <= 0)
    //             break;

    //         $balance = $sch->balance > 0
    //             ? $sch->balance
    //             : ($sch->installment + $sch->penalty_amount - $sch->amount_paid);

    //         $toPay = min($remaining, $balance);

    //         $sch->amount_paid += $toPay;
    //         $sch->balance -= $toPay;
    //         $sch->status = $sch->balance > 0 ? 'Partial Paid' : 'Paid';

    //         if ($sch->balance <= 0) {
    //             $sch->balance = 0;
    //             $sch->date_paid = now();
    //         }

    //         $sch->receipt_image = $binaryImage;
    //         $sch->save();

    //         $remaining -= $toPay;
    //                dd($cur);
    //     }
    // }




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

        $filename = ($coop->name ?? 'Cooperative') . '_' . (($coopProgram->start_date)->format('Y-m-d') ?? 'Cooperative') . '_Amortization.pdf';
        return $pdf->download($filename);
    }

    public function markIncomplete($id)
    {
        $coopProgram = CoopProgram::findOrFail($id);
        $coopProgram->program_status = null;
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
            $schedule->status = 'Resolved';
            $schedule->date_paid = now();
            $schedule->save();
        }


        return redirect()->back()->with('success', 'Loan marked as resolved and all schedules set to Paid.');
    }




}
