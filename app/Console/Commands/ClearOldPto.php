<?php

namespace App\Console\Commands;

use App\PaidTimeOff;
use Illuminate\Console\Command;

class ClearOldPto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee:clear-old-pto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear previous years PTO for all employees';

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
        $end = $year . '-12-31 23:59:50';
        $affected = PaidTimeOff::where('end_time', '<=', $end)->delete();

        $this->line($affected . ' ptos, on or before ' . $end . ', have been removed.');
    }
}
