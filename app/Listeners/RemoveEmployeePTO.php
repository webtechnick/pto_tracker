<?php

namespace App\Listeners;

use App\Events\EmployeeDeleting;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemoveEmployeePTO
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  EmployeeDeleting  $event
     * @return void
     */
    public function handle(EmployeeDeleting $event)
    {
        if ($event->employee) {
            $event->employee->ptos()->delete();
        }
    }
}
