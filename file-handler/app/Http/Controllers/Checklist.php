<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChecklistItem;
use App\Models\CooperativeUploads;
use App\Models\Cooperative;
use App\Models\Loan;
class Checklist extends Controller
{
    public function show($cooperativeId)
    {
        $cooperative = Cooperative::with('program')->findOrFail($cooperativeId);

        // Filter checklist items depending on program
        if (in_array($cooperative->program_id, [3, 5])) {

            $checklistItems = ChecklistItem::all();
        } else {

            $checklistItems = ChecklistItem::whereBetween('id', [1, 24])->get();
        }

        // Attach uploads for each item
        foreach ($checklistItems as $item) {
            $item->upload = CooperativeUploads::where('cooperative_id', $cooperativeId)
                ->where('checklist_item_id', $item->id)
                ->first();
        }
        $allUploaded = $checklistItems->every(fn($item) => $item->upload);
        $loan = Loan::where('cooperative_id', $cooperativeId)
            ->where('program_id', $cooperative->program_id)
            ->first();

        return view('checklist', compact('checklistItems', 'cooperative', 'loan', 'allUploaded'));
    }


    public function upload(Request $request, $cooperativeId)
    {
        $request->validate([
            'checklist_item_id' => 'required|exists:checklist_items,id',
            'file' => 'required|file|max:5120',
        ]);

        $file = $request->file('file');

        // Check if this cooperative already has an upload for this checklist item
        $existingUpload = CooperativeUploads::where('cooperative_id', $cooperativeId)
            ->where('checklist_item_id', $request->checklist_item_id)
            ->first();

        if ($existingUpload) {

            $existingUpload->delete();
        }

        // Save the new upload
        CooperativeUploads::create([
            'cooperative_id' => $cooperativeId,
            'checklist_item_id' => $request->checklist_item_id,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_content' => file_get_contents($file->getRealPath()),
        ]);

        try {
            $this->creatloan($cooperativeId);
        } catch (\Exception $e) {
            // catch any unexpected errors to prevent breaking the upload
            \Log::error("Loan creation failed: " . $e->getMessage());
        }
        return back()->with('success', 'File uploaded successfully and old file replaced!');


    }

    public function delete($id)
    {
        $upload = CooperativeUploads::findOrFail($id);
        $upload->delete();

        return back()->with('success', 'File deleted successfully!');

    }

    public function download($id)
    {
        $upload = CooperativeUploads::findOrFail($id);

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