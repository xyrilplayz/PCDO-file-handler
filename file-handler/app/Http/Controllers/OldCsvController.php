<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Old;
use App\Models\Cooperative;
use App\Models\CoopProgram;
use App\Models\Programs;
use Illuminate\Support\Facades\Response;

class OldCsvController extends Controller
{
    /**
     * List all saved CSVs grouped by program and cooperative
     */
    public function index()
    {
        // Load all programs with their coop programs and cooperatives
        $programs = Programs::with(['coopProgram.cooperative'])->get();

        // Attach CSVs to each coop program
        foreach ($programs as $program) {
            foreach ($program->coopProgram as $coopProgram) {
                $coopProgram->olds = Old::where('coop_program_id', $coopProgram->id)->get();
            }
        }

        return view('old', compact('programs'));
    }

    /**
     * Show one program with its cooperatives (ongoing & finished)
     */
    public function show($id)
    {
        $program = Programs::with(['coopProgram.cooperative'])->findOrFail($id);

        $ongoing = $program->coopProgram->where('program_status', 'Ongoing');
        $finished = $program->coopProgram->where('program_status', 'Finished');

        // Attach CSVs to each coop program
        foreach ($program->coopProgram as $coopProgram) {
            $coopProgram->olds = Old::where('coop_program_id', $coopProgram->id)->get();
        }

        return view('showing', compact('program', 'ongoing', 'finished'));
    }

    /**
     * View the CSV content in a table
     */
    public function view($id)
    {
        $record = Old::with('cooperative')->findOrFail($id);

        $csvData = str_getcsv($record->file_content, "\n"); // split by line
        $rows = array_map(fn($row) => str_getcsv($row), $csvData);

        return view('view', compact('rows', 'record'));
    }

    /**
     * Download the CSV file
     */
    public function download($id)
    {
        $record = Old::with('cooperative')->findOrFail($id);

        $coopName = $record->cooperative->name ?? 'coop';

        // Fetch the coop program correctly
        $program = CoopProgram::find($record->coop_program_id);
        $programName = $program->name ?? 'program';

        return Response::make($record->file_content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$coopName}_{$programName}_{$id}.csv\"",
        ]);
    }
}
