<?php

namespace App\Http\Controllers;

use App\Models\PaymentSchedule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PaymentScheduleController extends Controller
{
    // Show all schedules for a loan
    public function index($loanId)
    {
        $schedules = PaymentSchedule::where('loan_id', $loanId)->get();
        return view('schedules.index', compact('schedules', 'loanId'));
    }
    

}
