<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewUserRegisteredEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $profileData;

    /**
     * Create a new event instance.
     * @param User $user The newly registered user
     * @param array|null $profileData Additional data (group profile or visitor profile)
     */
    public function __construct(User $user, $profileData = null)
    {
        $this->user = $user;
        $this->profileData = $profileData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Private channel reserved for admins
        return new PrivateChannel('admin.notifications');
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'user.registered';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'user' => $this->user->toArray(),
            'profile_data' => is_object($this->profileData) ? $this->profileData->toArray() : $this->profileData
        ];
    }
}
