<?php

namespace App\Http\Controllers;

use App\Models\Moa;
use App\Models\CoopProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MoaController extends Controller
{
    public function index($id)
    {
        $coopProgram = CoopProgram::with('moas')->findOrFail($id);
        return view('moa_storing', compact('coopProgram'));
    }
}