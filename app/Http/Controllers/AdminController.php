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
        // Génération d'un mot de passe sécurisé à 6 caractères (min, MAJ, chiffre)
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits = '0123456789';
        $all = $lower . $upper . $digits;
        
        do {
            $codeArray = [
                $lower[random_int(0, strlen($lower) - 1)],
                $upper[random_int(0, strlen($upper) - 1)],
                $digits[random_int(0, strlen($digits) - 1)],
            ];
            
            // Compléter avec 3 caractères aléatoires
            for ($i = 0; $i < 3; $i++) {
                $codeArray[] = $all[random_int(0, strlen($all) - 1)];
            }
            
            // Mélanger les caractères pour éviter un format prédictible
            shuffle($codeArray);
            $newCode = implode('', $codeArray);
            
        } while (AccessCode::where('code', $newCode)->exists());

        $code = AccessCode::create([
            'code' => $newCode,
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

        \Illuminate\Support\Facades\DB::transaction(function () use ($group, $user, $accessCode, $id) {
            // Free up the access code so it can be used again
            if ($accessCode) {
                $accessCode->update(['is_used' => false]);
            }

            // Delete the group profile
            $group->delete();

            // Notify visitors via WebSocket
            try {
                broadcast(new \App\Events\GroupDeletedEvent($id));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Broadcast failed on group deletion: " . $e->getMessage());
            }

            // Delete the associated user account
            if ($user) {
                $user->delete();
            }
        });

        return response()->json(['success' => true]);
    }

    public function destroyVisitor($id)
    {
        $visitor = \App\Models\VisitorProfile::findOrFail($id);
        
        $user = $visitor->user;

        \Illuminate\Support\Facades\DB::transaction(function () use ($visitor, $user) {
            $visitor->delete();

            if ($user) {
                $user->delete();
            }
        });

        event(new \App\Events\VisitorDeletedEvent($id));

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

        // Note: l'événement n'est PAS diffusé ici. Il le sera seulement quand l'admin clique "Valider".
        return response()->json(['success' => true, 'event' => $event]);
    }

    public function updateEvent(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'publish' => 'nullable|boolean',
        ]);

        $updateData = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
        ];

        // Si l'admin demande la publication (touche Valider)
        if ($request->boolean('publish')) {
            $updateData['is_published'] = true;
        }

        $event->update($updateData);

        // On ne diffuse QUE si l'événement est publié ou s'il l'était déjà
        if ($event->is_published) {
            try {
                broadcast(new \App\Events\NewEventPublishedEvent($event, 'updated'));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Broadcast failed on event update/publish: ' . $e->getMessage());
            }
        }

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
 
            if ($event->is_published) {
                try {
                    broadcast(new \App\Events\NewEventPublishedEvent($event, 'updated'));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Broadcast failed on image upload: ' . $e->getMessage());
                }
            }
 
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
 
            if ($event->is_published) {
                try {
                    broadcast(new \App\Events\NewEventPublishedEvent($event, 'updated'));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Broadcast failed on video upload: ' . $e->getMessage());
                }
            }
 
            return response()->json(['success' => true, 'url' => asset('VIDEO/events/' . $fileName), 'type' => 'video']);
        }

        return response()->json(['success' => false, 'message' => 'Aucun média valide fourni.'], 400);
    }

    public function destroyEvent($id)
    {
        try {
            $event = Event::findOrFail($id);

            // Optionnel: Supprimer les fichiers physiques associés
            if ($event->image_path && \Illuminate\Support\Facades\File::exists(public_path($event->image_path))) {
                \Illuminate\Support\Facades\File::delete(public_path($event->image_path));
            }
            if ($event->video_path && \Illuminate\Support\Facades\File::exists(public_path($event->video_path))) {
                \Illuminate\Support\Facades\File::delete(public_path($event->video_path));
            }

            // Send broadcast before deletion so we have the ID and data
            // Isolated try/catch so a WebSocket error doesn't block the deletion
            try {
                broadcast(new \App\Events\NewEventPublishedEvent($event, 'deleted'));
            } catch (\Throwable $broadcastEx) {
                \Illuminate\Support\Facades\Log::warning('Broadcast failed on event deletion: ' . $broadcastEx->getMessage());
            }

            $event->delete();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('destroyEvent error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
