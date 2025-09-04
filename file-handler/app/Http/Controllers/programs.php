<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Cooperative;



class Programs extends Controller
{
    // Show the "Add Cooperative" form
    public function create()
    {
        $programs = Program::all(); // fetch all programs
        return view('cooperatives.create', compact('programs'));

    }

    // Store the cooperative in the database
    public function store(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'name' => 'required|string|max:255',
        ]);

        Cooperative::create([
            'program_id' => $request->program_id,
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Cooperative added successfully.');
    }
}