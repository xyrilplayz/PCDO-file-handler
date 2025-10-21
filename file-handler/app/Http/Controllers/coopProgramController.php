<?php

namespace App\Http\Controllers;
use App\Models\Notifications;
use App\Models\CoopProgram;
use App\Models\CoopProgramChecklist;
use App\Models\FinishedCoopProgram;
use App\Models\FinishedCoopProgramChecklist;
use App\Models\Cooperative;
use App\Models\Programs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\CoopProgramEnrolled;
use App\Models\AmmortizationSchedule;

class CoopProgramController extends Controller
{
    /**
     * Display a listing of coop programs.
     */
    public function index()
    {
        $coopPrograms = CoopProgram::with(['program', 'cooperative'])
            ->where('program_status', 'Ongoing')
            ->get();

        return view('createprogram', compact('coopPrograms'));
    }

    /**
     * Show the form for creating a new coop program.
     */
    public function create()
    {
        $cooperatives = Cooperative::all();
        $programs = Programs::all();

        return view('createprogram', compact('cooperatives', 'programs'));
    }

    /**
     * Store a newly created coop program (without loan details yet).
     */

    public function store(Request $request)
    {
        $data = $request->validate([
            'coop_id' => 'required|exists:cooperatives,id',
            'program_id' => 'required|exists:programs,id',
            'project' => 'required|string|max:255'
        ]);

        $program = Programs::findOrFail($data['program_id']);
        $cooperative = Cooperative::findOrFail($data['coop_id']);

        // Prevent duplicate ongoing programs
        $ongoingPrograms = CoopProgram::where('coop_id', $cooperative->id)
            ->where('program_status', 'Ongoing')
            ->with('program')
            ->get();

        foreach ($ongoingPrograms as $ongoing) {
            if ($ongoing->program_id === $program->id) {
                return back()->withErrors(['program_id' => 'This program is already ongoing.']);
            }
            if ($program->name === 'LICAP' && $ongoing->program->name === 'LICAP') {
                return back()->withErrors(['program_id' => 'LICAP program already ongoing.']);
            }
            if ($program->name !== 'LICAP' && $ongoing->program->name !== 'LICAP') {
                return back()->withErrors(['program_id' => 'Cannot enroll in another non-LICAP program while one is ongoing.']);
            }
        }

        // Create coop program
        $coopProgram = CoopProgram::create([
            'coop_id' => $cooperative->id,
            'program_id' => $program->id,
            'project' => $data['project'],
            'start_date' => now(),
            'end_date' => now()->addMonths($program->term_months),
            'program_status' => 'Ongoing',
            'loan_amount' => null,
            'with_grace' => null,
        ]);

        // Log notification of enrollment
        Notifications::create([
            'schedule_id' => null,
            'coop_id' => $cooperative->id,
            'type' => 'enrolled', // or define a new one like 'enrolled'
            'subject' => 'Cooperative Enrolled in Program',
            'body' => "The cooperative '{$cooperative->name}' has been enrolled in the '{$program->name}' program on " . now()->format('F j, Y') . ".",
            'processed' => 1,
        ]);

        if ($cooperative->coopDetail && $cooperative->coopDetail->email) {
            Mail::to($cooperative->coopDetail->email)
                ->send(new CoopProgramEnrolled($cooperative, $program));
        }
        return redirect()->route('checklists.show', $coopProgram->id)
            ->with('success', 'Program enrolled successfully. Notification logged.');
    }


    /**
     * Finalize loan details after checklist completion.
     */
    public function finalizeLoan(Request $request, CoopProgram $coopProgram)
    {
        $request->validate([
            'loan_ammount' => 'required|numeric|min:1',
            'with_grace' => 'required|boolean',
        ]);

        $program = $coopProgram->program;

        if ($request->loan_ammount < $program->min_amount || $request->loan_ammount > $program->max_amount) {
            return back()->withErrors([
                'loan_ammount' => "Loan amount must be between ₱{$program->min_amount} and ₱{$program->max_amount}"
            ]);
        }

        // ✅ Ensure checklist complete
        $allChecklists = $program->checklists()->count();
        $completed = $coopProgram->checklists()->whereNotNull('file_name')->count();

        if ($completed > $allChecklists) {
            return back()->withErrors(['loan_amount' => 'Checklist is not yet complete.']);
        }

        // ✅ Update loan details
        $coopProgram->update([
            'loan_ammount' => $request->loan_ammount,
            'with_grace' => $request->with_grace ? 4 : 0,
        ]);

        // ✅ Generate schedule automatically
        $monthsToPay = $program->term_months - $coopProgram->with_grace;
        if ($monthsToPay <= 0) {
            throw new \Exception('Invalid term and grace period.');
        }

        $amountPerMonth = intdiv($coopProgram->loan_ammount, $monthsToPay);
        $remainder = $coopProgram->loan_ammount % $monthsToPay;
        $startDate = now()->addMonths($coopProgram->with_grace);

        $firstDueDate = $startDate->copy();

        for ($i = 1; $i <= $monthsToPay; $i++) {
            $amountDue = $amountPerMonth;
            if ($i === $monthsToPay) {
                $amountDue += $remainder;
            }

            AmmortizationSchedule::create([
                'coop_program_id' => $coopProgram->id,
                'due_date' => $startDate->copy()->addMonths($i - 1),
                'installment' => $amountDue,
                'status' => 'Unpaid',
            ]);
        }

        // ✅ Send Email Notification
        $coop = $coopProgram->cooperative;
        $coopDetail = $coop->coopDetail;
        

        if ($coopDetail && $coopDetail->email) {
            $subject = 'Amortization Schedule Created';
            $body = "Dear {$coop->name},\n\nYour amortization schedule has been successfully generated under the program '{$program->name}'.\nYour first payment of ₱{$coopProgram->loan_ammount} is due on " . $firstDueDate->format('F d, Y') . ".\n\nThank you.";

            Mail::raw($body, function ($message) use ($coopDetail, $subject) {
                $message->to($coopDetail->email)
                    ->subject($subject);
            });
        }

        // ✅ Log Notification in DB
        Notifications::create([
            'schedule_id' => null,
            'coop_id' => $coop->id,
            'type' => 'has_schedule',
            'subject' => 'Amortization Schedule Created',
            'body' => "The cooperative '{$coop->name}' has been issued an amortization schedule under the '{$program->name}' program. First due date: " . $firstDueDate->format('F d, Y') . ".",
            'processed' => 1,
        ]);

        return redirect()->route('loan.tracker.show', $coopProgram->id)
            ->with('success', 'Loan finalized and amortization schedule generated successfully!');
    }

    /**
     * Display a specific coop program.
     */
    public function show(CoopProgram $coopProgram)
    {
        $coopProgram->load(['program', 'cooperative', 'ammortizationSchedules']);
        return view('coop-program', compact('coopProgram'));
    }

    /**
     * Delete a coop program.
     */
    public function destroy(CoopProgram $coopProgram)
    {
        $coopProgram->delete();
        return back()->with('success', 'Program deleted successfully!');
    }

    public function archiveFinishedProgram($coopProgramId)
    {
        DB::transaction(function () use ($coopProgramId) {
            // 1. Get the coop program
            $coopProgram = CoopProgram::with('checklists.uploads')->findOrFail($coopProgramId);

            if ($coopProgram->program_status !== 'Finished' || $coopProgram->exported !== 1) {
                throw new \Exception('Program must be finished and exported before archiving.');
            }

            // 2. Move CoopProgram to FinishedCoopProgram
            $finished = FinishedCoopProgram::create([
                'coop_id' => $coopProgram->coop_id,
                'program_id' => $coopProgram->program_id,
                'start_date' => $coopProgram->start_date,
                'end_date' => $coopProgram->end_date,
                'program_status' => $coopProgram->program_status,
                'loan_amount' => $coopProgram->loan_amount,
                'with_grace' => $coopProgram->with_grace,
                'email' => $coopProgram->email,
                'number' => $coopProgram->number,
                'exported' => true,
            ]);

            // 3. Move Checklists + Uploads
            foreach ($coopProgram->checklists as $checklist) {
                foreach ($checklist->uploads as $upload) {
                    FinishedCoopProgramChecklist::create([
                        'finished_coop_program_id' => $finished->id,
                        'checklist_id' => $checklist->checklist_id,
                        'is_completed' => true,
                        'file_name' => $upload->file_name,
                        'mime_type' => $upload->mime_type,
                        'file_content' => $upload->file_content,
                    ]);
                }
            }

            // 4. Delete original records
            CoopProgramChecklist::where('coop_program_id', $coopProgram->id)->delete();
            $coopProgram->delete();
        });

        return redirect()->route('programs.index')->with('success', 'Program archived successfully!');
    }
}
