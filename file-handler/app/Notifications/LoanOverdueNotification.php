<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LoanOverdueNotification extends Notification
{
    use Queueable;

    protected $loan;

    public function __construct($loan)
    {
        $this->loan = $loan;
    }
    public function via($notifiable)
    {
        return ['mail'];
    }



    public function toMail($notifiable)
    {
        // Find the earliest unpaid schedule due within 3 days or already overdue
        $dueSchedule = $this->loan->paymentSchedules()
            ->where('is_paid', false)
            ->whereDate('due_date', '<=', now()->addDays(3))
            ->orderBy('due_date', 'asc')
            ->first();

        if (!$dueSchedule) {
            return (new MailMessage)
                ->subject('Loan Payment Status')
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('Currently, you have no upcoming or overdue payments.');
        }

        // Work out the status message
        if ($dueSchedule->due_date->isToday()) {
            $statusText = "Your payment is due today.";
        } elseif ($dueSchedule->due_date->isPast()) {
            $daysOverdue = $dueSchedule->due_date->diffInDays(now());
            $days = intval($daysOverdue);
            $statusText = "Your payment is overdue by {$days} day(s).";
        } else {
            $daysLeft = now()->diffInDays($dueSchedule->due_date);
            $statusText = intval("Your payment is due in {$daysLeft} day(s).");
        }

        $dueDateText = $dueSchedule->due_date->format('F d, Y');

        // Total due (without penalties)
        $totalDue = $this->loan->paymentSchedules()
            ->where('is_paid', false)
            ->whereDate('due_date', '<=', now()->addDays(3))
            ->sum('amount_due');

        // Total due including penalties
        $penalty = $this->loan->paymentSchedules()
            ->where('is_paid', false)
            ->whereDate('due_date', '<=', now()->addDays(3))
            ->get()
            ->sum(function ($schedule) {
                return ($schedule->amount_due * 0.01) + $schedule->amount_due;
            });



        return (new MailMessage)
            ->subject('Loan Payment Reminder')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($statusText)
            ->line('Due Date: ' . $dueDateText)
            ->line('Amount to pay: ₱' . number_format($totalDue, 2))
            ->line('Amount to pay with penalty: ₱' . number_format($penalty, 2))
            ->line('Please settle your payment as soon as possible to avoid additional penalties.');
    }


}