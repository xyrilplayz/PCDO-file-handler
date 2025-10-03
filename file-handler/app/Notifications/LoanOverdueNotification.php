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

        $dueDateText = $schedule->due_date->format('F d, Y');

        // Calculate amounts
        $totalDue = $schedule->installment;
        $penalty = ($schedule->installment * 0.01) + $schedule->installment;
        $type = null;
        // Status text
        if ($schedule->due_date->isToday()) {
            $statusText = "Your payment is due today.";
            $type = 'due_today';
        } elseif (now()->diffInDays($schedule->due_date, false) == 3) {
            $statusText = "Your payment is due in 3 day(s).";
            $type = 'due_soon';
        } elseif (now()->diffInDays($schedule->due_date, false) < 0) {
            $days = abs(now()->diffInDays($schedule->due_date, false));
            $statusText = "Your payment is overdue by {$days} day(s).";
            $type = 'overdue';
        } else {
            $days = now()->diffInDays($schedule->due_date);
            $statusText = "Your payment is due in {$days} day(s).";
            $type = 'due_in';
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

        $subject = $mail->subject;
        $body = implode("\n", $mail->introLines);

        // ✅ Save to DB
        $pending = $schedule->pendingnotifications()
            ->where('processed', 0)
            ->first();

        if ($pending) {
            // Update existing pending row
            $pending->update([
                'subject' => $subject,
                'body' => $body,
                'processed' => 1,
            ]);
        } else {
            // No pending row → insert directly into notifications
                Notifications::create([
                'schedule_id' => $schedule->id,
                'coop_id' => $coopProgram->coop_id,
                'type' => $type,
                'subject' => $subject,
                'body' => $body,
                'processed' => 1,
            ]);
        }

        return $mail;
    }

}
