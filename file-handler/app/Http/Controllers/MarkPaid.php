<?php
namespace App\Http\Controllers;
use App\Models\PaymentSchedule;
use Illuminate\Http\Request;
class markPaid extends Controller
{
    public function markPaid($id)
    {
        $schedule = PaymentSchedule::findOrFail($id);
        $schedule->markPaid();

        return back()->with('success', 'Payment marked as paid.');
    }
    public function notePayment(Request $request, $id)
    {
        $schedule = PaymentSchedule::findOrFail($id);

        $request->validate([
            'amount_paid' => 'required|numeric|min:0',
        ]);


        $schedule->amount_paid =+ $request->amount_paid;

        $schedule->balance = ($schedule->amount_due + $schedule->penalty_amount) - $schedule->amount_paid;

        if ($schedule->balance <= 0) {
            $schedule->is_paid = true;
            $schedule->paid_at = now();
            $schedule->balance = 0; // avoid negatives
        }

        $schedule->save();

        return back()->with('success', 'Payment noted successfully.');
    }


}



?>