<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CoopDetail;
use App\Models\Cooperative;

class CoopDetailsController extends Controller
{
    public function index($id)
    {
        $coop = Cooperative::with('details')->findOrFail($id);

        return view('show', compact('coop'));
    }


}