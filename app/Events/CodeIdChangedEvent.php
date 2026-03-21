<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CodeIdChangedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $groupProfileId;
    public string $newCodeId;

    public function __construct(int $groupProfileId, string $newCodeId)
    {
        $this->groupProfileId = $groupProfileId;
        $this->newCodeId = $newCodeId;
    }

    public function broadcastOn()
    {
        return new Channel('admin.notifications');
    }

    public function broadcastAs(): string
    {
        return 'codeid.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'group_profile_id' => $this->groupProfileId,
            'new_code_id' => $this->newCodeId,
        ];
    }
}
