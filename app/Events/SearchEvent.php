<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SearchEvent implements ShouldBroadcast
{
    use SerializesModels;

    public $rider_id;
    public $searchResults;

    public function __construct($rider_id, $searchResults)
    {
        $this->rider_id = $rider_id;
        $this->searchResults = $searchResults;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('nearby-riders');
    }
}
