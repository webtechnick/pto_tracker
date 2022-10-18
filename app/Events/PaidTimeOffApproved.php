<?php

namespace App\Events;

use App\PaidTimeOff;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaidTimeOffApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pto;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(PaidTimeOff $pto)
    {
        $this->pto = $pto;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
