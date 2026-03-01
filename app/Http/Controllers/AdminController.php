<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccessCode;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class AdminController extends Controller
{
    public function index()
    {
        $accessCodes = AccessCode::where('is_used', false)->orderBy('created_at', 'desc')->get();
        $groupProfiles = \App\Models\GroupProfile::with(['user', 'accessCode'])->get();
        $visitorProfiles = \App\Models\VisitorProfile::with('user')->orderBy('created_at', 'desc')->get();
        $events = Event::orderBy('created_at', 'desc')->get();
        
        return view('admin', compact('accessCodes', 'groupProfiles', 'visitorProfiles', 'events'));
    }

    public function storeCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:access_codes,code|max:255'
        ]);

        $code = AccessCode::create([
            'code' => $request->code,
            'is_used' => false
        ]);

        return response()->json(['success' => true, 'code' => $code]);
    }

    public function destroyCode($id)
    {
        $code = AccessCode::findOrFail($id);
        $code->delete();

        return response()->json(['success' => true]);
    }

    public function destroyGroup($id)
    {
        $group = \App\Models\GroupProfile::findOrFail($id);
        
        // Find associated user and access code
        $user = $group->user;
        $accessCode = $group->accessCode;

        \Illuminate\Support\Facades\DB::transaction(function () use ($group, $user, $accessCode) {
            // Free up the access code so it can be used again
            if ($accessCode) {
                $accessCode->update(['is_used' => false]);
            }

            // Delete the group profile
            $group->delete();

            // Delete the associated user account
            if ($user) {
                $user->delete();
            }
        });

        return response()->json(['success' => true]);
    }

    // --- EVENTS MANAGEMENT ---

    public function storeEvent(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $event = Event::create([
            'title' => $request->title,
        ]);

        return response()->json(['success' => true, 'event' => $event]);
    }

    public function updateEvent(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $event->update($validated);

        return response()->json(['success' => true, 'event' => $event]);
    }

    public function uploadEventMedia(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:20480', // 20MB
            ]);
            
            $file = $request->file('image');
            $fileName = time() . '_event_img_' . $file->getClientOriginalName();
            $file->move(public_path('IMG/events'), $fileName);
            $event->update(['image_path' => 'IMG/events/' . $fileName]);

            return response()->json(['success' => true, 'url' => asset('IMG/events/' . $fileName), 'type' => 'image']);
        }

        if ($request->hasFile('video')) {
            $request->validate([
                'video' => 'mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime,video/webm|max:512000', // 500MB
            ]);

            $file = $request->file('video');
            $fileName = time() . '_event_vid_' . $file->getClientOriginalName();
            $file->move(public_path('VIDEO/events'), $fileName);
            $event->update(['video_path' => 'VIDEO/events/' . $fileName]);

            return response()->json(['success' => true, 'url' => asset('VIDEO/events/' . $fileName), 'type' => 'video']);
        }

        return response()->json(['success' => false, 'message' => 'Aucun média valide fourni.'], 400);
    }

    public function destroyEvent($id)
    {
        $event = Event::findOrFail($id);

        // Optionnel: Supprimer les fichiers physiques associés
        if ($event->image_path && File::exists(public_path($event->image_path))) {
            File::delete(public_path($event->image_path));
        }
        if ($event->video_path && File::exists(public_path($event->video_path))) {
            File::delete(public_path($event->video_path));
        }

        $event->delete();

        return response()->json(['success' => true]);
    }
}
