<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resolved;
use App\Models\CoopProgram;
use App\Models\AmmortizationSchedule;

class ResolvedController extends Controller
{
    /**
     * Show the upload form (optional)
     */
    public function create($coopProgramId)
    {
        $coopProgram = CoopProgram::findOrFail($coopProgramId);
        return view('resolved.upload', compact('coopProgram'));
    }

    /**
     * Store uploaded proof and mark schedules as paid
     */
    public function store(Request $request, $coopProgramId)
    {
        $request->validate([
            'file' => 'required|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
        ]);

        $coopProgram = CoopProgram::findOrFail($coopProgramId);

        // Read file as binary
        $fileContent = file_get_contents($request->file('file')->getRealPath());

        // Save into "resolved" table
        $resolved = Resolved::create([
            'coop_program_id' => $coopProgram->id,
            'file_content' => $fileContent,
        ]);

        // Mark all amortization schedules as Paid
        AmmortizationSchedule::where('coop_program_id', $coopProgram->id)
            ->update([
                'status' => 'Resolved',
                'date_paid' => now(),
                'balance' => 0,
                'penalty_amount' => 0,
            ]);

        // Mark program as Finished
        $coopProgram->update(['program_status' => 'Resolved']);

        return redirect()->back()->with('success', '✅ Program marked as resolved and all payments marked Paid.');
    }

    /**
     * Download the uploaded proof
     */
    public function download($id)
    {
        $resolved = Resolved::findOrFail($id);
        $fileContent = $resolved->file_content;

        return response($fileContent)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="receipt_' . $resolved->id . '.pdf"');
    }
}
