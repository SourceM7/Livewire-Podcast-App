<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $listeninigPartyId;
    public $message;
    /**
     * Create a new event instance.
     */
    public function __construct($listeninigPartyId, $message)
    {
        $this->listeninigPartyId = $listeninigPartyId;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new Channel('listening-party'.$this->listeninigPartyId);
    }

    public function broadcastAs()
    {
        return 'new-message';
    }
}
