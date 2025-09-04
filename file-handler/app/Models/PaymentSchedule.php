<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'due_date',
        'amount_due',
        'is_paid',
        'paid_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'date',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function markPaid()
    {
        $this->update([
            'is_paid' => true,
            'paid_at' => now()
        ]);
    }
    protected $dates = ['due_date']; 

}

?>