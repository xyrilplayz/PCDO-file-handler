<?php

namespace App\Http\Controllers;

use App\Models\CoopProgram;
use App\Models\CoopProgramProgress;
use DateTime;
use Illuminate\Http\Request;
use App\Models\Programs;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver; // ✅ Import the driver

class CoopProgramProgressController extends Controller
{
    public function show(CoopProgramProgress $report)
    {

        $imageData = base64_decode($report->file_content);

        return response($imageData)
            ->header('Content-Type', $report->mime_type);
    }
    public function create(Programs $program)
    {
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
            'file.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $fileName = null;
        $mimeType = null;
        $fileContent = null;

        if ($request->hasFile('file')) {
            $files = $request->file('file');
            $count = count($files);

            // ✅ Use Intervention v3 manager
            $manager = new ImageManager(new Driver());

            if ($count > 1) {
                // Create a collage dynamically
                $canvasSize = 800;
                $canvas = $manager->create($canvasSize, $canvasSize);

                $grid = ceil(sqrt($count));
                $cellSize = (int) ($canvasSize / $grid);

                foreach ($files as $index => $file) {
                    $x = ($index % $grid) * $cellSize;
                    $y = floor($index / $grid) * $cellSize;

                    $img = $manager->read($file)->cover($cellSize, $cellSize);
                    $canvas->place($img, 'top-left', $x, $y);
                }

                $fileName = 'Progress on ' . date('Y-m-d') . '.jpg';
                $mimeType = 'image/jpeg';
                $fileContent = base64_encode($canvas->toJpeg(90));
            } else {
                // ✅ Single image upload
                $file = $files[0];
                $img = $manager->read($file);

                $fileName = $file->getClientOriginalName();
                $mimeType = 'image/jpeg';
                $fileContent = base64_encode($img->toJpeg(90));
            }
        }

        CoopProgramProgress::create([
            'coop_program_id' => $data['coop_program_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'file_name' => $fileName,
            'mime_type' => $mimeType,
            'file_content' => $fileContent,
        ]);

        return redirect()->back()->with('success', 'Progress report with collage added successfully!');
    }


    public function download(CoopProgramProgress $report)
    {
        return response(base64_decode($report->file_content))
            ->header('Content-Type', $report->mime_type)
            ->header('Content-Disposition', 'attachment; filename="' . $report->file_name . '"');
    }
}
