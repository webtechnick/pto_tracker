<?php

namespace App\Console\Commands;

use App\Employee;
use Illuminate\Console\Command;

class ClearProratedPtoLimit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee:clear-pto-limit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the PTO limit for prorated employees. This should be run at the beginning of every year.';

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
        $max = config('app.max_days_off');
        $employees = Employee::where(['max_days_off', '<', $max])
                             ->update(['max_days_off' => $max]);

        $this->line($employees . ' employee(s) set to default: ' . $max);
    }
}
