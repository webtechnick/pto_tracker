<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ShuffleOnCall::class,
        Commands\DisplayOnCall::class,
        Commands\SendOnCallDigest::class,
        Commands\ClearProratedPtoLimit::class,
        Commands\ClearOldPto::class,
        Commands\ClearOldHoliday::class,
        Commands\EmployeeUserSync::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Shuffle the on call weekly on mondays at 1AM.
        // $schedule->command('oncall:shuffle')->weekly()->mondays()->at('14:00');

        // Attempt to Sync employees daily at 2AM
        $schedule->command('employee:user-sync')->dailyAt('15:00');
        // Clear Prorated PTO limit for new employees yearly on Jan 1st at 1 AM
        $schedule->command('employee:clear-pto-limit')->yearly()->at('01:00');
        // Clear old PTO to keep database lean - Jan 1st at 2 AM
        $schedule->command('employee:clear-old-pto')->yearly()->at('02:00');
        // Clear old holidays to keep database lean - Jan 1st at 3 AM
        $schedule->command('holiday:clear-old')->yearly()->at('03:00');
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
