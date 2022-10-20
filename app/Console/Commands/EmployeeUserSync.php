<?php

namespace App\Console\Commands;

use App\Employee;
use App\User;
use Illuminate\Console\Command;

class EmployeeUserSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee:user-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attempt to sync unassigned user accounts with employee records.';

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
        // Get all users without an employee attached
        $users = User::select(['id','name','employee_id'])
                     ->whereNull('employee_id')
                     ->get();

        $count = 0;
        foreach($users as $user) {
            $employee = Employee::select(['id','name'])
                                ->where('name', $user->name)
                                ->first();

            // We found an employee that matches, link them.
            if ($employee) {
                $user->employee_id = $employee->id;
                $user->save();
                $count++;
            }
        }

        $this->line($count . ' users/employees have been newly synced.');
    }
}
