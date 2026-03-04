<?php

namespace App\Events;

use App\Models\Event;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewEventPublishedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $event;
    public $action;

    /**
     * Create a new event instance.
     * @param Event $event
     * @param string $action 'created', 'updated', or 'deleted'
     */
    public function __construct(Event $event, string $action = 'created')
    {
        $this->event = $event;
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Public channel to update visitors in real-time
        return new Channel('public.updates');
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'event.published';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        $event = $this->event;
        return [
            'action' => $this->action,
            'event' => [
                'id'          => $event->id,
                'title'       => $event->title,
                'description' => $event->description ?? '',
                'image'       => $event->image_path ? url($event->image_path) : null,
                'video'       => $event->video_path ? url($event->video_path) : null,
                'created_at'  => $event->created_at,
                'updated_at'  => $event->updated_at,
            ],
        ];
    }
}
