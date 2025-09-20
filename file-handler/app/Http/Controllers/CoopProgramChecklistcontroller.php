<?php

namespace App\Http\Controllers;

use App\Models\ProgramChecklists;
use Illuminate\Http\Request;
use App\Models\CoopProgram;
use App\Models\CooperativeUploads;
use App\Models\Cooperative;
use App\Models\Checklists;
use App\Models\Loan;
use App\Models\CoopProgramChecklist;
class CoopProgramChecklistcontroller extends Controller
{
    // public function show($coopProgramid)
    // {
    //     $cooperative = CoopProgram::with(['program', 'cooperative'])
    //         ->findOrFail($coopProgramid);

    //     // Base query with uploads filtered by coop_program_id
    //     $query = Checklists::with([
    //         'uploads' => function ($q) use ($coopProgramid) {
    //             $q->where('coop_program_id', $coopProgramid);
    //         }
    //     ]);

    //     // Apply conditions based on program_id
    //     if (in_array($cooperative->program_id, [3, 5])) {
    //         $checklistItems = $query->get(); // all checklist items
    //     } else {
    //         $checklistItems = $query->whereBetween('id', [1, 24])->get();
    //     }

    //     return view('checklist', compact('cooperative', 'checklistItems'));
    // }

    //ito ung pag same lng na checklist for each program
    public function show($coopProgramid)
{
    $cooperative = CoopProgram::with(['program', 'cooperative'])
        ->findOrFail($coopProgramid);

    // Get ALL coop_program IDs for this cooperative + program (USAD etc.)
    $coopProgramIds = CoopProgram::where('coop_id', $cooperative->coop_id)
        ->where('program_id', $cooperative->program_id)
        ->pluck('id');

    // Base query with uploads across all availments of this program
    $query = Checklists::with([
        'uploads' => function ($q) use ($coopProgramIds) {
            $q->whereIn('coop_program_id', $coopProgramIds);
        }
    ]);

    // Apply conditions based on program_id
    if (in_array($cooperative->program_id, [3, 5])) {
        $checklistItems = $query->get(); // all checklist items
    } else {
        $checklistItems = $query->whereBetween('id', [1, 24])->get();
    }

    return view('checklist', compact('cooperative', 'checklistItems'));
}


    public function upload(Request $request, $coopProgramId)
    {
        $request->validate([
            'program_checklist_id' => 'required|exists:checklists,id',
            'file' => 'required|file|max:5120',
        ]);

        $file = $request->file('file');

        // Check if this CoopProgram already has an upload for this checklist item
        $existingUpload = CoopProgramChecklist::where('coop_program_id', $coopProgramId)
            ->where('program_checklist_id', $request->program_checklist_id)
            ->first();

        if ($existingUpload) {
            $existingUpload->delete();
        }

        // Save the new upload
        CoopProgramChecklist::create([
            'coop_program_id' => $coopProgramId,
            'program_checklist_id' => $request->program_checklist_id,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_content' => file_get_contents($file->getRealPath()),
        ]);


        return back()->with('success', 'File uploaded successfully and old file replaced!');
    }

    public function delete($id)
    {
        $upload = CoopProgramChecklist::findOrFail($id);
        $upload->delete();

        return back()->with('success', 'File deleted successfully!');

    }

    public function download($id)
    {
        $upload = CoopProgramChecklist::findOrFail($id);

        return response($upload->file_content)
            ->header('Content-Type', $upload->mime_type)
            ->header('Content-Disposition', 'attachment; filename="' . $upload->file_name . '"');
    }



    public function searchUploads(Request $request)
    {
        $query = CooperativeUploads::query()
            ->with(['cooperative.program', 'checklistItem']);

        if ($request->filled('program_id')) {
            $query->whereHas('cooperative', function ($q) use ($request) {
                $q->where('program_id', $request->program_id);
            });
        }

        if ($request->filled('search')) {
            $query->whereHas('cooperative', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%');
            });
        }

        $uploads = $query->paginate(10);
        $programs = \App\Models\Program::all();

        return view('checklist_search_uploads', compact('uploads', 'programs'));
    }
    private function creatloan($cooperativeId)
    {
        $cooperative = Cooperative::with('program')->find($cooperativeId);

        if (!$cooperative || !$cooperative->program) {
            return;
        }

        $requiredItems = in_array($cooperative->program_id, [2, 5])
            ? ChecklistItem::count()
            : ChecklistItem::whereBetween('id', [1, 24])->count();

        $uploadedCount = CooperativeUploads::where('cooperative_id', $cooperativeId)->count();

        // ⚠️ for production use >=
        if ($uploadedCount <= $requiredItems) {
            $existingLoan = Loan::where('cooperative_id', $cooperativeId)
                ->where('program_id', $cooperative->program_id)
                ->first();

            if (!$existingLoan) {
                $loan = Loan::create([
                    'cooperative_id' => $cooperativeId,
                    'program_id' => $cooperative->program_id,
                    'amount' => $cooperative->program->max_amount,
                    'start_date' => now(),
                    'grace_period' => $cooperative->with_grace,
                    'term_months' => $cooperative->program->term_months,
                ]);

                $loan->generateSchedule();
            }
        }
    }

}