<?php

namespace App\Http\Controllers;

use App\Models\CoopProgram;
use App\Models\CoopProgramChecklist;
use App\Models\FinishedCoopProgram;
use App\Models\FinishedCoopProgramChecklist;
use App\Models\Cooperative;
use App\Models\Programs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'coop_email' => 'required|email',
            'program_id' => 'required|exists:programs,id',
            'number' => 'required|numeric',
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

        // Create coop program (loan details are NULL initially)
        $coopProgram = CoopProgram::create([
            'coop_id' => $cooperative->id,
            'program_id' => $program->id,
            'project' => $data ['project'],
            'start_date' => now(),
            'end_date' => now()->addMonths($program->term_months),
            'program_status' => 'Ongoing',
            'number' => $data['number'],
            'email' => $data['coop_email'],
            'loan_amount' => null,
            'with_grace' => null,
        ]);

        return redirect()->route('checklists.show', $coopProgram->id)
            ->with('success', 'Program enrolled successfully. Please complete required documents.');
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

        // Ensure checklist is complete
        $allChecklists = $program->checklists()->count();
        $completed = $coopProgram->checklists()->whereNotNull('file_name')->count();

        if ($allChecklists < $completed) {
            return back()->withErrors(['loan_amount' => 'Checklist is not yet complete.']);
        }

        $coopProgram->update([
            'loan_ammount' => $request->loan_ammount,
            'with_grace' => $request->with_grace ? 4 : 0,
        ]);

        return back()->with('success', 'Loan details finalized successfully!');
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
