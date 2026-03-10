<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $groupData;

    /**
     * Create a new event instance.
     *
     * @param array|object $groupData
     */
    public function __construct($groupData)
    {
        $this->groupData = $groupData;
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
        return 'group.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        $group = $this->groupData;

        // If it's not an object (e.g. array), return it as is or try to format
        if (!is_object($group)) {
            return ['group' => $group];
        }

        $domains = [];
        if (!empty($group->project_domain)) {
            $domains = array_map('trim', explode(',', $group->project_domain));
        }

        $formattedGroup = [
            'id'      => $group->id,
            'user_id' => $group->user_id,
            'name'    => $group->project_name,
            'last_modified' => $group->updated_at ? $group->updated_at->toIso8601String() : now()->toIso8601String(),
            'image' => $group->project_image ? asset($group->project_image) : asset('ICON/group.svg'), 
            'leader' => $group->leader_name,
            'niveau' => $group->leader_level,
            'filiere' => $group->leader_sector,
            'domains' => $domains,
            'intro' => $group->project_intro,
            'members' => $group->relationLoaded('members') && $group->members ? $group->members->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'sector' => $member->sector,
                    'level' => $member->level,
                ];
            })->toArray() : [],
            'reports' => $group->relationLoaded('reports') && $group->reports ? $group->reports->map(function ($report) {
                return [
                    'id' => $report->id,
                    'file_name' => $report->file_name,
                    'file_url' => asset($report->file_path),
                ];
            })->toArray() : [],
            'video' => $group->project_video ? asset($group->project_video) : null,
            'whatsapp' => $group->contact_whatsapp,
            'email' => $group->contact_email,
        ];

        return [
            'group' => $formattedGroup
        ];
    }
}
