<?php

namespace App\Mail;

use App\PaidTimeOff;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaidTimeOffApproved extends Mailable
{
    use Queueable, SerializesModels;

    public $pto = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(PaidTimeOff $pto)
    {
        $this->pto = $pto;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.pto.approved');
    }
}
