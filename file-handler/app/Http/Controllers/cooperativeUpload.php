<?php

namespace App\Http\Controllers;

use App\Models\CooperativeUploads;
use Illuminate\Http\Request;

class CooperativeUpload extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'cooperative_id' => 'required|exists:cooperatives,id',
            'checklist_item_id' => 'required|exists:checklist_items,id',
            'file' => 'required|file|max:5120',
        ]);

        $file = $request->file('file');

        // Clean the filename to avoid malformed UTF-8
        $originalName = $file->getClientOriginalName();
        $safeName = mb_convert_encoding($originalName, 'UTF-8', 'UTF-8'); // enforce UTF-8
        $safeName = preg_replace('/[^\x20-\x7E]/', '', $safeName); // remove weird chars

        CooperativeUploads::create([
            'cooperative_id' => $request->cooperative_id,
            'checklist_item_id' => $request->checklist_item_id,
            'file_name' => $safeName,
            'mime_type' => $file->getClientMimeType(),
            'file_content' => base64_encode(file_get_contents($file->getRealPath())),
        ]);

        return back()->with('success', 'File uploaded successfully!');
    }


    public function download($id)
    {
        $upload = CooperativeUploads::findOrFail($id);

        // Fallback to a safe filename if corrupted
        $filename = preg_replace('/[^\x20-\x7E]/', '', $upload->file_name) ?: 'downloaded_file';

        return response($upload->file_content)
            ->header('Content-Type', $upload->mime_type)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

}
