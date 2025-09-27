<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CoopProgram;
use App\Models\Checklists;
use App\Models\CoopProgramChecklist;

class CoopProgramChecklistController extends Controller
{
    /**
     * Show the checklist for a coop program.
     */
    public function show($coopProgramId)
    {
        $coopProgram = CoopProgram::with(['program', 'cooperative'])->findOrFail($coopProgramId);

        // base checklist items
        $checklistItems = $coopProgram->program->checklists;

        // Add conditional logic by program name
        if ($coopProgram->program->name === 'USAD SULONG COPSE') {
            $checklistItems = $checklistItems->take(24); // only 24
        } elseif (in_array($coopProgram->program->name, ['LICAP', 'PCLRP'])) {
            $checklistItems = $checklistItems->take(26); // 26
        }

        return view('checklist', [
            'cooperative' => $coopProgram,
            'checklistItems' => $checklistItems,
        ]);
    }
    /**
     * Upload a file for a checklist item.
     */
    public function upload(Request $request, $coopProgramId)
    {
        $request->validate([
            'program_checklist_id' => 'required|exists:checklists,id',
            'file' => 'required|file|max:5120',
        ]);

        $file = $request->file('file');

        // Replace old file if exists
        $existingUpload = CoopProgramChecklist::where('coop_program_id', $coopProgramId)
            ->where('program_checklist_id', $request->program_checklist_id)
            ->first();

        if ($existingUpload) {
            $existingUpload->delete();
        }

        CoopProgramChecklist::create([
            'coop_program_id' => $coopProgramId,
            'program_checklist_id' => $request->program_checklist_id,
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
