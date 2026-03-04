<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageToVisitorEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Diffuse sur un canal privé spécifique au visiteur
        return [
            new PrivateChannel('visitor.messages.' . $this->message->visitor_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'reply.received';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        // On charge le profil du groupe pour renvoyer le vrai nom/image au visiteur
        $this->message->loadMissing(['group:id,name', 'group.groupProfile:user_id,project_name,project_image']);
        
        // Append project_name as done in the controller
        if ($this->message->group && $this->message->group->groupProfile) {
            $this->message->group->project_name = $this->message->group->groupProfile->project_name;
            $this->message->group->project_image = $this->message->group->groupProfile->project_image;
        } else {
            $this->message->group->project_name = $this->message->group->name ?? 'Groupe';
        }

        return [
            'message' => $this->message->toArray()
        ];
    }
}
