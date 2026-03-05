<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\GroupProfile;
use App\Models\Event;

class VisitorController extends Controller
{
    public function index()
    {
        // Fetch all group profiles with their members and reports
        $groups = GroupProfile::with(['members', 'reports'])->get();

        // Map groups to the format expected by JS
        $groupsData = $groups->map(function ($group) {
            $domains = [];
            if (!empty($group->project_domain)) {
                $domains = array_map('trim', explode(',', $group->project_domain));
            }

            return [
                'id'      => $group->id,          // GroupProfile.id (for display)
                'user_id' => $group->user_id,      // User.id (for messaging API)
                'name'    => $group->project_name,
                'last_modified' => $group->updated_at ? $group->updated_at->toIso8601String() : now()->toIso8601String(),
                'image' => $group->project_image ? asset($group->project_image) : asset('ICON/group.svg'), 
                'leader' => $group->leader_name,
                'niveau' => $group->leader_level,
                'filiere' => $group->leader_sector,
                'domains' => $domains,
                // Include real intro if available
                'intro' => $group->project_intro,
                // Map the eloquent members relation
                'members' => $group->members->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'sector' => $member->sector,
                        'level' => $member->level,
                    ];
                })->toArray(),
                'reports' => $group->reports->map(function ($report) {
                    return [
                        'id' => $report->id,
                        'file_name' => $report->file_name,
                        'file_url' => asset($report->file_path),
                    ];
                })->toArray(),
                'video' => $group->project_video ? asset($group->project_video) : null,
                'whatsapp' => $group->contact_whatsapp,
                'email' => $group->contact_email,
            ];
        });

        // Fetch only published events
        $events = Event::where('is_published', true)->orderBy('event_date', 'desc')->get();

        // Map events to format expected by JS
        $eventsData = $events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description ?? '',
                'image' => $event->image_path ? asset($event->image_path) : null,
                'video' => $event->video_path ? asset($event->video_path) : null,
            ];
        });

        return view('visiteur', [
            'groupsData' => $groupsData,
            'eventsData' => $eventsData,
        ]);
    }
}
