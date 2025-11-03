<?php

use App\Console\Commands\ExportCompletedLoans;
use Illuminate\Support\Facades\Artisan;



Artisan::command('loans:export-completed', function () {
    $this->call('export:completed-loans');
});

//Schedule::command('notification:process')->everyMinute(); can be half a day
// Schedule::command('export:completed-loans')->everyMinute();
// Schedule::command('archive:coop-programs')->everyMinute();
// Schedule::command('cleanup:coop-programs')->everyMinute();
// Schedule::command('check:delinquents')->every();
//check due payments  everyday
//cleanup from pending notiiffs every half a day