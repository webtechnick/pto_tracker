<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| Closure based commands and the application's command schedule. Commands
| in app/Console/Commands are discovered automatically.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Shuffle the on call weekly on mondays at 1AM.
// Schedule::command('oncall:shuffle')->weekly()->mondays()->at('14:00');

// Attempt to Sync employees daily at 2AM
Schedule::command('employee:user-sync')->dailyAt('15:00');
// Clear Prorated PTO limit for new employees yearly on Jan 1st at 1 AM
Schedule::command('employee:clear-pto-limit')->yearly()->at('01:00');
// Clear old PTO to keep database lean - Jan 1st at 2 AM
Schedule::command('employee:clear-old-pto')->yearly()->at('02:00');
// Clear old holidays to keep database lean - Jan 1st at 3 AM
Schedule::command('holiday:clear-old')->yearly()->at('03:00');
