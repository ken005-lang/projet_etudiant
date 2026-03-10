<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VisitorDeletedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $visitorId;

    public function __construct($visitorId)
    {
        $this->visitorId = $visitorId;
    }

    public function broadcastOn()
    {
        return new Channel('public.updates');
    }

    public function broadcastAs()
    {
        return 'visitor.deleted';
    }
}
