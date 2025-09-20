<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Old;
use Illuminate\Support\Facades\Response;

class OldCsvController extends Controller
{
    // List all saved CSVs
    public function index()
    {
        $files = Old::latest()->get();
        return view('old', compact('files'));
    }

    // View CSV content in a table
    public function view($id)
    {
        $record = Old::findOrFail($id);

        $csvData = str_getcsv($record->file_content, "\n"); // split rows
        $rows = array_map(fn($row) => str_getcsv($row), $csvData);

        return view('view', compact('rows'));
    }

    // Download CSV
    public function download($id)
    {
        $record = Old::findOrFail($id);

        return Response::make($record->file_content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"completed_loans_{$id}.csv\"",
        ]);
    }
}
