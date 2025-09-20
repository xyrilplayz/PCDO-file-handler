<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LoanOverdueNotification extends Notification
{
    use Queueable;

    protected $schedule;

    public function __construct($schedule)
    {
        $this->schedule = $schedule;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $greetingName = $notifiable->name ?? 'PCDO';
        $schedule = $this->schedule;
        $coopProgram = $schedule->coopProgram;

        $dueDateText = $schedule->due_date->format('F d, Y');

        // Calculate amounts
        $totalDue = $schedule->installment;
        $penalty = ($schedule->installment * 0.01) + $schedule->installment;

        // Determine status text
        if ($schedule->due_date->isToday()) {
            $statusText = "Your payment is due today.";
        } elseif (now()->diffInDays($schedule->due_date, false) == 3) {
            $statusText = "Your payment is due in 3 day(s).";
        } elseif (now()->diffInDays($schedule->due_date, false) < 0) {
            $days = abs(now()->diffInDays($schedule->due_date, false));
            $statusText = "Your payment is overdue by {$days} day(s).";
        } else {
            $days = now()->diffInDays($schedule->due_date);
            $statusText = "Your payment is due in {$days} day(s).";
        }

        return (new MailMessage)
            ->subject('Loan Payment Reminder')
            ->greeting('Hello ' . $greetingName . ',')
            ->line($statusText)
            ->line('Coop Program: ' . ($coopProgram?->name ?? 'Unknown'))
            ->line('Due Date: ' . $dueDateText)
            ->line('Amount to pay: ₱' . number_format($totalDue, 2))
            ->line('Amount to pay with penalty: ₱' . number_format($penalty, 2))
            ->line('Please settle your payment as soon as possible to avoid additional penalties.');
    }
}
