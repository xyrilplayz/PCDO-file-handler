<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loan;
use App\Notifications\LoanOverdueNotification;
use Carbon\Carbon;

class SendLoanReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for loans that are due in 3 days, due today, or overdue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        $loans = Loan::with(['paymentSchedules', 'cooperative.user'])->get();

        foreach ($loans as $loan) {
            foreach ($loan->paymentSchedules as $schedule) {
                if ($schedule->is_paid) {
                    continue; // skip paid schedules
                }

                $dueDate = Carbon::parse($schedule->due_date);

                // 3 days before due
                if ($today->equalTo($dueDate->copy()->subDays(3))) {
                    $this->notifyUser($loan, $schedule, '3 days before due date');
                }

                // On due date
                if ($today->equalTo($dueDate)) {
                    $this->notifyUser($loan, $schedule, 'on due date');
                }

                // 1 day after due (only once, not daily)
                if ($today->equalTo($dueDate->copy()->addDay())) {
                    $this->notifyUser($loan, $schedule, 'overdue');
                }
            }
        }

        $this->info('Loan reminders processed successfully.');
    }

    protected function notifyUser($loan, $schedule, $type)
    {
        if ($loan->cooperative && $loan->cooperative->user) {
            $loan->cooperative->user->notify(new LoanOverdueNotification($loan));
            $this->info("Notification sent to {$loan->cooperative->user->email} ({$type})");
        }
    }
}
