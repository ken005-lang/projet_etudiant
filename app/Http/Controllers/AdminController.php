<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccessCode;

class AdminController extends Controller
{
    public function index()
    {
        $accessCodes = AccessCode::where('is_used', false)->orderBy('created_at', 'desc')->get();
        $groupProfiles = \App\Models\GroupProfile::with(['user', 'accessCode'])->get();
        $visitorProfiles = \App\Models\VisitorProfile::with('user')->orderBy('created_at', 'desc')->get();
        
        return view('admin', compact('accessCodes', 'groupProfiles', 'visitorProfiles'));
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
}
