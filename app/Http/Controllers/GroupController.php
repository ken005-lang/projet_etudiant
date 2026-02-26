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
}
