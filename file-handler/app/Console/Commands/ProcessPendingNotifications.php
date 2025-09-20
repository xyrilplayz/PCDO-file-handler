<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PendingNotification;
use App\Notifications\LoanOverdueNotification;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class ProcessPendingNotifications extends Command
{
    protected $signature = 'notifications:process';
    protected $description = 'Process pending loan notifications and send emails';

    public function handle()
    {
        // Fetch unprocessed notifications
        $notifications = PendingNotification::where('processed', 0)->get();

        if ($notifications->isEmpty()) {
            $this->info('No pending notifications to process.');
            return SymfonyCommand::SUCCESS;
        }

        foreach ($notifications as $notif) {
            $schedule = $notif->schedule;

            if (!$schedule) {
                $this->error("Schedule ID {$notif->schedule_id} not found.");
                continue;
            }

            $coopProgram = $schedule->coopProgram;
            $email = $coopProgram?->email;
            $coopName = $coopProgram?->name ?? 'Unknown Coop Program';

            if ($email) {
                try {
                    // Send notification using the updated LoanOverdueNotification
                    Notification::route('mail', $email)
                        ->notify(new LoanOverdueNotification($schedule));

                    $this->info("✅ Notification sent to {$email} for {$coopName}, type: {$notif->type}");

                    // Mark notification as processed
                    $notif->processed = 1;
                    $notif->save();
                } catch (\Exception $e) {
                    $this->error("❌ Failed to send notification to {$email}: " . $e->getMessage());
                }
            } else {
                $this->warn("❌ No email found for coop program ID {$schedule->coop_program_id}");
            }
        }

        return SymfonyCommand::SUCCESS;
    }
}
