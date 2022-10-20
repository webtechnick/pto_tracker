<?php

namespace App\Listeners;

use App\Events\PaidTimeOffRequested;
use App\Mail\PaidTimeOffRequested as MailPTORequest;
use App\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class NotifyManagerOfPTORequest
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
     * @param  PaidTimeOffRequested  $event
     * @return void
     */
    public function handle(PaidTimeOffRequested $event)
    {
        $pto = $event->pto;

        // Figure out manager to email.
        $managers = $pto->employee->manager ?: User::allManagers()->get();

        // Send Mail
        Mail::to($managers)->send(new MailPTORequest($pto));
    }
}
