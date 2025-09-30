<?php

use App\Console\Commands\ExportCompletedLoans;
use Illuminate\Support\Facades\Artisan;



Artisan::command('loans:export-completed', function () {
    $this->call('export:completed-loans');
});

Schedule::command('notification:process')->everyMinute();
Schedule::command('export:completed-loans')->everyMinute();
Schedule::command('archive:coop-programs')->everyMinute();
// Schedule::command('cleanup:coop-programs')->everyMinute();