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

        // Clear Prorated PTO limit for new employees yearly on the first
        $schedule->command('employee:clear-pto-limit')->yearly();
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
