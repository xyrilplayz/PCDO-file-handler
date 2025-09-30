<?php

namespace App\Http\Controllers;
use App\Models\CoopProgram;
use App\Models\CoopProgramProgress;
use Illuminate\Http\Request;
use App\Models\Programs;


class CoopProgramProgressController extends Controller
{
    public function create(Programs $program)
    {
        // Only cooperatives enrolled in this program
        $coopPrograms = CoopProgram::with('cooperative')
            ->where('program_id', $program->id)
            ->get();

        return view('progress_reports', compact('program', 'coopPrograms'));
    }

    public function store(Request $request, Programs $program)
    {
        $data = $request->validate([
            'coop_program_id' => 'required|exists:coop_programs,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:5120',
        ]);

        $fileName = null;
        $mimeType = null;
        $fileContent = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $fileContent = base64_encode(file_get_contents($file));
        }

        CoopProgramProgress::create([
            'coop_program_id' => $data['coop_program_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'file_name' => $fileName,
            'mime_type' => $mimeType,
            'file_content' => $fileContent,
        ]);

        return redirect()->back()->with('success', 'Progress report added successfully!');
    }
    public function download(CoopProgramProgress $report)
    {
        return response(base64_decode($report->file_content))
            ->header('Content-Type', $report->mime_type)
            ->header('Content-Disposition', 'attachment; filename="' . $report->file_name . '"');
    }
}
