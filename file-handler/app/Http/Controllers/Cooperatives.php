<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\CoopProgram;
use App\Models\Cooperative;
use App\Models\CoopDetail;
use Illuminate\Support\Facades\Auth;


class Cooperatives extends Controller
{
    // Show create form
    // Show all cooperatives (homepage)
    public function index()
    {
        $cooperatives = Cooperative::all(); // or paginate if many
        return view('welcome', compact('cooperatives'));
    }

    // Show create form
    public function coop()
    {
        $cooperatives = Cooperative::all(); // for holder dropdown
        return view('create', compact('cooperatives'));

    }

    // Handle cooperative creation
    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'name' => 'required|string|max:255',
            'holder' => 'nullable|exists:cooperatives,id',
            'type' => 'required|in:primary,secondary,tertiary',
            'contact_number' => 'required|string|max:20',
            'email' => 'required|email|max:255',
        ]);

        $existing = Cooperative::where('id', $request->id)
            ->where('name', $request->name)
            ->first();

        if ($existing) {
            return view('coop_exists', [
                'cooperative' => $existing
            ]);
        }

        // Create the cooperative
        $cooperative = Cooperative::create([
            'id' => $request->id,
            'name' => $request->name,
            'holder' => $request->holder,
            'type' => $request->type,
        ]);

        // Save additional details
        CoopDetail::create([
            'coop_id' => $cooperative->id,
            'number' => $request->contact_number,
            'email' => $request->email,
        ]);

        return redirect()->route('cooperatives.create')
            ->with('success', 'Cooperative created successfully.');
    }
    public function getDetails($id)
    {
        $coop = Cooperative::with('coopDetail')->findOrFail($id);
        return response()->json([
            'number' => optional($coop->coopDetail)->number,
            'email' => optional($coop->coopDetail)->email,
        ]);
    }



    public function show($id)
    {
        $cooperative = Cooperative::with([
            'coopProgram.program',
            'oldPrograms',
            'progressReports'
        ])->findOrFail($id);

        return view('progress', compact('cooperative'));
    }


    // // Handle post request
    // public function creatcoopPost(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'program_id' => 'required|exists:programs,id',
    //     ]);

    //     // Check if cooperative already exists under this program
    //     $existing = CoopProgram::where('program_id', $request->program_id)
    //         ->where('name', $request->name)
    //         ->first();

    //     if ($existing) {
    //         return view('coop_exists', [
    //             'cooperative' => $existing
    //         ]);
    //     }

    //     // Create cooperative
    //     //dd($request->all());

    //     $gracePeriod = $request->boolean('without_grace') ? 0 : 4;

    //     $cooperative = Cooperative::create([
    //         'name' => $request->name,
    //         'program_id' => $request->program_id,
    //         'user_id' => auth()->id(),
    //         'with_grace' => $gracePeriod,
    //     ]);

    //     return redirect()->route('checklist.show', $cooperative->id)
    //         ->with('success', 'Cooperative created successfully!');
    // }

}
