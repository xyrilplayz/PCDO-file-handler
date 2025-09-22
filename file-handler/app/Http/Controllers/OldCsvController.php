<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Old;
use Illuminate\Support\Facades\Response;
use App\Models\CoopProgram;
use App\Models\Programs;

class OldCsvController extends Controller
{
    // List all saved CSVs
    public function index()
    {
        // Load programs with coop programs, cooperatives, and old CSVs
        $programs = Programs::with(['coopProgram.cooperative', 'coopProgram.olds'])
            ->get();

        return view('old', compact('programs'));
    }

    /**
     * Show one program with its cooperatives (ongoing & finished).
     */
    public function show($id)
    {
        $program = Programs::with(['coopProgram.cooperative', 'coopProgram.olds'])
            ->findOrFail($id);

        // Separate ongoing and finished
        $ongoing = $program->coopProgram->where('program_status', 'Ongoing');
        $finished = $program->coopProgram->where('program_status', 'Finished');

        return view('showing', compact('program', 'ongoing', 'finished'));
    }

    /**
     * View the CSV content in a table.
     */
    public function view($id)
    {
        $record = Old::with('coopProgram.cooperative', 'coopProgram.program')
            ->findOrFail($id);

        $csvData = str_getcsv($record->file_content, "\n"); // split by line
        $rows = array_map(fn($row) => str_getcsv($row), $csvData);

        return view('view', compact('rows', 'record'));
    }

    /**
     * Download the CSV file.
     */
    public function download($id)
    {
        $record = Old::with('coopProgram.cooperative', 'coopProgram.program')
            ->findOrFail($id);

        $coopName = $record->coopProgram->cooperative->name ?? 'coop';
        $programName = $record->coopProgram->program->name ?? 'program';

        return Response::make($record->file_content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$coopName}_{$programName}_{$id}.csv\"",
        ]);
    }
}
