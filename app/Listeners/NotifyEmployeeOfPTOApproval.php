<?php

namespace App\Listeners;

use App\Events\PaidTimeOffApproved;
use App\Mail\PaidTimeOffApproved as MailApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class NotifyEmployeeOfPTOApproval
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
     * @param  PaidTimeOffApproved  $event
     * @return void
     */
    public function handle(PaidTimeOffApproved $event)
    {
        $pto = $event->pto;

        // If employee is assigned to a user, send mail
        if ($pto->employee->user) {
            Mail::to($pto->employee->user)->send(new MailApproved($pto));
        }
    }
}
