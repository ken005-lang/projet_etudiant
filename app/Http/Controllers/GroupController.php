<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index()
    {
        $group = \Illuminate\Support\Facades\Auth::user()->groupProfile;
        return view('groupe', compact('group'));
    }

    public function uploadVideo(Request $request)
    {
        $request->validate([
            'video_file' => 'required|mimes:mp4,mov,ogg,qt,webm|max:512000', // 500MB max
        ]);

        $group = \Illuminate\Support\Facades\Auth::user()->groupProfile;

        if (!$group) {
            return response()->json(['error' => 'Group profile not found.'], 404);
        }

        if ($request->hasFile('video_file')) {
            $file = $request->file('video_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // Move file to public/VIDEO
            $file->move(public_path('VIDEO'), $fileName);
            
            // Save path to DB
            $videoPath = 'VIDEO/' . $fileName;
            $group->project_video = $videoPath;
            $group->save();

            return response()->json([
                'success' => true,
                'message' => 'Video uploaded successfully',
                'video_url' => asset($videoPath)
            ]);
        }

        return response()->json(['error' => 'No video file provided.'], 400);
    }

    public function updateProfile(Request $request)
    {
        $group = \Illuminate\Support\Facades\Auth::user()->groupProfile;

        if (!$group) {
            return response()->json(['error' => 'Group profile not found.'], 404);
        }

        $validated = $request->validate([
            'project_name' => 'nullable|string|max:255',
            'project_intro' => 'nullable|string|max:1000',
            'leader_level' => 'nullable|string|max:50',
            'project_domain' => 'nullable|string',
            'contact_whatsapp' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
        ]);

        $group->update($validated);

        return response()->json(['success' => true, 'message' => 'Profile updated']);
    }

    public function addMember(Request $request)
    {
        $group = \Illuminate\Support\Facades\Auth::user()->groupProfile;

        if (!$group) {
            return response()->json(['error' => 'Group profile not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sector' => 'required|string|max:255',
            'level' => 'required|string|max:50',
        ]);

        $member = $group->members()->create($validated);

        return response()->json(['success' => true, 'member' => $member]);
    }

    public function removeMember($id)
    {
        $group = \Illuminate\Support\Facades\Auth::user()->groupProfile;

        if (!$group) {
            return response()->json(['error' => 'Group profile not found.'], 404);
        }

        $member = $group->members()->find($id);

        if (!$member) {
            return response()->json(['error' => 'Member not found.'], 404);
        }

        $member->delete();

        return response()->json(['success' => true]);
    }

    public function uploadImage(Request $request)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,svg,gif,webp|max:20480',
            ]);

            if ($validator->fails()) {
                \Illuminate\Support\Facades\Log::error('Image validation failed', [
                    'all_errors' => $validator->errors()->toArray(),
                    'image_error' => $validator->errors()->first('image'),
                    'request_all' => $request->all(),
                    'has_file' => $request->hasFile('image')
                ]);
                return response()->json([
                    'success' => false, 
                    // Fallback to a generic message if first('image') is suspiciously empty
                    'error' => $validator->errors()->first('image') ?: 'Validation failed (unknown explanation)'
                ], 422);
            }

            $user = auth()->user();
            $group = \App\Models\GroupProfile::where('user_id', $user->id)->first();

            if (!$group) {
                return response()->json(['success' => false, 'error' => 'Groupe non trouvé'], 404);
            }

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . $file->getClientOriginalName();
                
                // Ensure directory exists
                $uploadDir = public_path('IMG/uploads');
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Move file to public/IMG/uploads
                $file->move($uploadDir, $filename);

                // Save path in database
                $path = 'IMG/uploads/' . $filename;
                $group->update(['project_image' => $path]);

                return response()->json([
                    'success' => true,
                    'image_url' => asset($path)
                ]);
            }

            \Illuminate\Support\Facades\Log::warning('No image received in uploadImage', ['request' => $request->all()]);
            return response()->json(['success' => false, 'error' => 'Aucune image reçue'], 400);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('uploadImage exception', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
}
