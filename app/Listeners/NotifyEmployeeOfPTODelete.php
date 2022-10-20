<?php

namespace App\Listeners;

use App\Events\PaidTimeOffDeleted;
use App\Mail\PaidTimeOffDeleted as MailDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class NotifyEmployeeOfPTODelete
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
     * @param  PaidTimeOffDeleted  $event
     * @return void
     */
    public function handle(PaidTimeOffDeleted $event)
    {
        $pto = $event->pto;

        // If PTO is in the past, do nothing.
        if ($pto->isPast()) {
            return;
        }

        // If employee is assigned to a user, send mail
        if ($pto->employee->user) {
            Mail::to($pto->employee->user)->send(new MailDeleted($pto));
        }
    }
}
