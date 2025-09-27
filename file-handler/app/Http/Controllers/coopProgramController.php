<?php

namespace App\Http\Controllers;

use App\Models\CoopProgram;
use App\Models\Cooperative;
use App\Models\Programs;
use Illuminate\Http\Request;

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
}
