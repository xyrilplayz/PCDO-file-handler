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
            'amount_paid' => 'required|numeric|min:0|max:' . $schedule->amount_due,
        ]);

        $schedule->amount_paid = $request->amount_paid;

        // Only mark fully paid if amount_paid >= amount_due
        $schedule->is_paid = $schedule->amount_paid >= $schedule->amount_due;

        $schedule->paid_at = $schedule->is_paid ? now() : null;

        $schedule->save();

        return back()->with('success', 'Payment noted successfully.');
    }

}



?>