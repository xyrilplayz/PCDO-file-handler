<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\pending_notifications;
use App\Models\PaymentSchedule;
use App\Notifications\LoanOverdueNotification;
use Symfony\Component\Console\Command\Command as SymfonyCommand;


class ProcessPendingNotifications extends Command
{
    protected $signature = 'notifications:process';
    protected $description = 'Process pending loan notifications and send emails';

    public function handle()
    {
        // Get all unprocessed notifications
        $notifications = pending_notifications::where('processed', 0)->get();

        foreach ($notifications as $notif) {
            $schedule = PaymentSchedule::find($notif->schedule_id);

            if (!$schedule) {
                $this->error("Schedule {$notif->schedule_id} not found");
                continue;
            }

            $loan = $schedule->loan;
            $coop = $loan->cooperative;

            // Assuming cooperative has a user/staff contact email
            if ($coop && $coop->user) {
                $coop->user->notify(new LoanOverdueNotification($loan));
                $this->info("✅ Notification sent for coop: {$coop->name}, type: {$notif->type}");

                // Mark as processed
                $notif->processed = 1;
                $notif->save();
            }
        }

        return SymfonyCommand::SUCCESS;

    }
}
