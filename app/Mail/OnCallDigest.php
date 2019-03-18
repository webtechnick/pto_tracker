<?php

namespace App\Mail;

use App\Employee;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OnCallDigest extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Carbon $start = null, Carbon $end = null)
    {
        $start = $start ?: Carbon::now();
        $this->start = $start;

        // Have to store in new variable or we inadvertantly update the start property
        // $end = $end ?: $start->addDays(7);
        $this->end = Carbon::now()->addDays(7);
        // $this->end = $end;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('On Call Schedule ' . $this->start->toFormattedDateString() . ' to ' . $this->end->toFormattedDateString())
                    ->view('emails.oncall.digest')
                    ->with([
                        'employees' => Employee::onCall()->get(),
                        'start' => $this->start,
                        'end' => $this->end
                    ]);
    }
}
