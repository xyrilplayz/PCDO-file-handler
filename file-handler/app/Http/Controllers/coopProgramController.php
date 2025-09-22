<?php

namespace App\Http\Controllers;

use App\Models\CoopProgram;
use App\Models\Cooperative;
use App\Models\Programs;
use App\Models\Checklists;
use Illuminate\Http\Request;

class CoopProgramController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $coopPrograms = CoopProgram::with(['program', 'cooperative', 'checklist'])
            ->where('program_status', 'Ongoing')
            ->get();

        return view('createprogram', compact('coopPrograms'));
    }

    public function create()
    {
        $cooperatives = Cooperative::all();
        $programs = Programs::all();

        return view('createprogram', compact('cooperatives', 'programs'));
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //dd($request->all());
        $data = $request->validate([
            'coop_id' => 'required|exists:cooperatives,id',
            'coop_email' => 'required|email',
            'program_id' => 'required|exists:programs,id',
            'loan_amount' => 'required|numeric|min:1',
            'with_grace' => 'required|boolean',
        ]);

        $program = Programs::findOrFail($data['program_id']);

        if ($data['loan_amount'] < $program->min_amount || $data['loan_amount'] > $program->max_amount) {
            return back()->withErrors([
                'loan_amount' => "Loan amount must be between ₱{$program->min_amount} and ₱{$program->max_amount}"
            ])->withInput();
        }

        $cooperative = Cooperative::findOrFail($data['coop_id']);

        // Check ongoing programs
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

        $coopProgram = CoopProgram::create([
            'coop_id' => $cooperative->id,
            'program_id' => $program->id,
            'start_date' => now(),
            'end_date' => now()->addMonths($program->term_months),
            'program_status' => 'Ongoing',
            'email' => $data['coop_email'],          // must match Blade input
            'loan_ammount' => $data['loan_amount'],  // must match Blade input
            'with_grace' => $data['with_grace'] ? 4 : 0,
        ]);


        return redirect()->route('login.post', $coopProgram->id)
            ->with('success', 'Program enrolled successfully. Please upload required documents.');
    }


    /**
     * Display the specified resource.
     */
    public function show(CoopProgram $coopProgram)
    {
        // Load related data if needed
        $coopProgram->load(['program', 'cooperative', 'ammortizationSchedules']);

        return view('coop-program', compact('coopProgram'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CoopProgram $coopProgram)
    {
        return view('coop-program.edit', compact('coopProgram'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CoopProgram $coopProgram)
    {
        // add update logic if needed
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoopProgram $coopProgram)
    {
        $coopProgram->delete();
        return back()->with('success', 'Program deleted successfully!');
    }

    public function schedule(CoopProgram $coopProgram)
    {
        return view('coop-program.schedule', compact('coopProgram'));
    }

    public function documents(CoopProgram $coopProgram)
    {
        // Fetch all checklist items for the program
        $checklistItems = $coopProgram->program->checklists;

        // Attach uploaded files from the pivot table
        foreach ($checklistItems as $item) {
            $upload = $coopProgram->checklists()
                ->where('program_checklist_id', $item->pivot->id ?? $item->id)
                ->first();

            $item->upload = $upload;
        }

        return view('coop-program.document', [
            'coopProgram' => $coopProgram->load('program'),
            'checklistItems' => $checklistItems,
        ]);
    }


    public function upload(Request $request, CoopProgram $coopProgram)
    {
        $request->validate([
            'program_checklist_id' => 'required|exists:program_checklists,id',
            'file' => 'required|file|max:5120',
        ]);

        $file = $request->file('file');

        $coopProgram->checklists()->updateExistingPivot(
            $request->program_checklist_id,
            [
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_content' => base64_encode(file_get_contents($file)),
                'updated_at' => now(),
            ]
        );

        return back()->with('success', 'File uploaded successfully!');
    }

    public function download(CoopProgram $coopProgram, $programChecklistId)
    {
        $pivot = $coopProgram->checklists()->where('program_checklist_id', $programChecklistId)->firstOrFail()->pivot;

        return response(base64_decode($pivot->file_content))
            ->header('Content-Type', $pivot->mime_type)
            ->header('Content-Disposition', 'attachment; filename="' . $pivot->file_name . '"');
    }

    public function destroyUpload(CoopProgram $coopProgram, $programChecklistId)
    {
        $coopProgram->checklists()->updateExistingPivot($programChecklistId, [
            'file_name' => null,
            'mime_type' => null,
            'file_content' => null,
        ]);

        return back()->with('success', 'File deleted successfully!');
    }
}
