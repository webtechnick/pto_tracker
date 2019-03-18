<?php

namespace App\Console\Commands;

use App\Mail\OnCallDigest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendOnCallDigest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oncall:digest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send the current on call digest email.';

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
        // send digest email notification to oncall@lacallegroup.com
        Mail::to('oncall@lacallegroup.com')->send(new OnCallDigest());

        $this->line('On call digest email sent.');
    }
}
