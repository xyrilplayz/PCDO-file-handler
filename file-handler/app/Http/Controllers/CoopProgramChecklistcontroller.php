<?php

namespace App\Http\Controllers;

use App\Models\Checklists;
use Illuminate\Http\Request;
use App\Models\CoopProgram;
use App\Models\ProgramChecklists;
use App\Models\CoopProgramChecklist;

class CoopProgramChecklistController extends Controller
{
    /**
     * Show the checklist for a coop program.
     */
    public function show($coopProgramId)
    {
        $coopProgram = CoopProgram::with(['program'])->findOrFail($coopProgramId);

        // Get all ProgramChecklists for this program
        $programChecklists = ProgramChecklists::with('uploads', 'checklist')
            ->where('program_id', $coopProgram->program_id)
            ->get();

        return view('checklist', [
            'cooperative' => $coopProgram,
            'checklistItems' => $programChecklists,
        ]);
    }

    /**
     * Upload a file for a checklist item.
     */
    public function upload(Request $request, $coopProgramId)
    {
        $request->validate([
            'program_checklist_id' => 'required|exists:program_checklists,id',
            'file' => 'required|file|max:5120', // 5 MB
        ]);

        $file = $request->file('file');

        // Check if a file already exists for this program & checklist
        $existingUpload = CoopProgramChecklist::where('coop_program_id', $coopProgramId)
            ->where('program_checklist_id', $request->program_checklist_id)
            ->first();

        if ($existingUpload) {
            $existingUpload->delete();
        }

        CoopProgramChecklist::create([
            'coop_program_id' => $coopProgramId,
            'program_checklist_id' => $request->program_checklist_id, // now correct pivot ID
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_content' => file_get_contents($file->getRealPath()),
        ]);

        return back()->with('success', 'File uploaded successfully!');
    }

    /**
     * Download a file.
     */
    public function download($id)
    {
        $upload = CoopProgramChecklist::findOrFail($id);

        return response($upload->file_content)
            ->header('Content-Type', $upload->mime_type)
            ->header('Content-Disposition', 'attachment; filename="' . $upload->file_name . '"');
    }


    /**
     * Delete an uploaded file.
     */
    public function delete($id)
    {
        $upload = CoopProgramChecklist::findOrFail($id);
        $upload->delete();

        return back()->with('success', 'File deleted successfully!');
    }
}
