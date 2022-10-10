<?php

namespace App\Console\Commands;

use App\Holiday;
use Illuminate\Console\Command;

class ClearOldHoliday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holiday:clear-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear previous holidays';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $year = date('Y') - 2;
        $end = $year . '-12-31';
        $affected = Holiday::where('date', '<=', $end)->delete();

        $this->line($affected . ' holidays, on or before ' . $end . ', have been removed.');
    }
}
