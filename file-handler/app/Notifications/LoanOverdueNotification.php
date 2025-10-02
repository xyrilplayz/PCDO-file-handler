<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\PendingNotification;
use App\Models\Notifications;

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
        $schedule = $this->schedule;
        $coopProgram = $schedule->coopProgram;
        $greetingName = $coopProgram->cooperative->name ?? 'PCDO';
        $notifications = $schedule->pendingnotifications;


        $dueDateText = $schedule->due_date->format('F d, Y');

        // Calculate amounts
        $totalDue = $schedule->installment;
        $penalty = ($schedule->installment * 0.01) + $schedule->installment;

        // Status text
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

        // Build the MailMessage
        $mail = (new MailMessage)
            ->subject('Loan Payment Reminder')
            ->greeting('Hello ' . $greetingName . ',')
            ->line($statusText)
            ->line('Coop Program: ' . ($coopProgram->program->name ?? 'Unknown'))
            ->line('Due Date: ' . $dueDateText)
            ->line('Amount to pay: ₱' . number_format($totalDue, 2))
            ->line('Amount to pay with penalty: ₱' . number_format($penalty, 2))
            ->line('Please settle your payment as soon as possible to avoid additional penalties.');

        $subject = 'Loan Payment Reminder';
        $body = implode("\n", [
            'Hello ' . $greetingName . ',',
            $statusText,
            'Coop Program: ' . ($coopProgram->program->name ?? 'Unknown'),
            'Due Date: ' . $dueDateText,
            'Amount to pay: ₱' . number_format($totalDue, 2),
            'Amount to pay with penalty: ₱' . number_format($penalty, 2),
            'Please settle your payment as soon as possible to avoid additional penalties.'
        ]);
        // ✅ Save to DB
        $notifications->subject = $subject;
        $notifications->body = $body;
        $notifications->processed = 1;
        $notifications->save();

        return $mail;
    }
}
