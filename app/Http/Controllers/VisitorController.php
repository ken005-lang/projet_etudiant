<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\GroupProfile;
use App\Models\Event;

class VisitorController extends Controller
{
    public function index()
    {
        // Fetch all group profiles
        $groups = GroupProfile::all();

        // Map groups to the format expected by JS
        $groupsData = $groups->map(function ($group) {
            $domains = [];
            if (!empty($group->project_domain)) {
                $domains = array_map('trim', explode(',', $group->project_domain));
            }

            return [
                'id' => $group->id,
                'name' => $group->project_name,
                'last_modified' => $group->updated_at ? $group->updated_at->toIso8601String() : now()->toIso8601String(),
                'image' => 'IMG/rites_placeholder.png', // Fallback since no DB column yet
                'leader' => $group->leader_name,
                'niveau' => $group->leader_level,
                'filiere' => $group->leader_sector,
                'domains' => $domains,
                'members' => [], // Empty array for now, waiting for members table mapping
                'video' => $group->project_video ? asset($group->project_video) : null,
                'whatsapp' => null, // Empty for now
                'email' => null, // Empty for now
            ];
        });

        // Fetch all events
        $events = Event::orderBy('event_date', 'desc')->get();

        // Map events to format expected by JS
        $eventsData = $events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'image' => $event->image_path,
                'video' => $event->video_path,
            ];
        });

        return view('visiteur', [
            'groupsData' => $groupsData,
            'eventsData' => $eventsData,
        ]);
    }
}
