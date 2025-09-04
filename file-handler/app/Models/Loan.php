<?php
namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
class Loan extends Model
{
    public function paymentSchedules()
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    /**
     * Generate schedules once the loan starts
     */
    public function generateSchedule()
    {
        $monthsToPay = $this->term_months - $this->grace_period;

        if ($monthsToPay <= 0) {
            throw new \Exception('Invalid term and grace period.');
        }

        $amountPerMonth = round($this->amount / $monthsToPay, 2);
        $startDate = Carbon::parse($this->start_date)->addMonths($this->grace_period);

        for ($i = 1; $i <= $monthsToPay; $i++) {
            // ✅ Adjust last payment to fix rounding issues
            $amountDue = ($i === $monthsToPay)
                ? $this->amount - ($amountPerMonth * ($monthsToPay - 1))
                : $amountPerMonth;

            $this->paymentSchedules()->create([
                'due_date' => $startDate->copy()->addMonths($i - 1),
                'amount_due' => $amountDue,
            ]);
        }
    }

    protected $fillable = [
        'cooperative_id',
        'program_id',
        'amount',
        'start_date',
        'grace_period',
        'term_months',
    ];
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class, 'cooperative_id');
    }

}


?>