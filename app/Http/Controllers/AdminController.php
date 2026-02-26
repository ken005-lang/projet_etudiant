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
        return view('admin', compact('accessCodes', 'groupProfiles'));
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
}
