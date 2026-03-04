<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Envoyer un nouveau message (depuis le Visiteur)
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000'
        ]);

        $visitor = Auth::user();
        
        if ($visitor->type_role !== 'visiteur') {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $message = Message::create([
            'visitor_id' => $visitor->id,
            'group_id' => $request->group_id,
            'visitor_message' => $request->message,
            'is_read_by_group' => false,
            'is_read_by_visitor' => true, // Le visiteur l'a écrit, il n'a pas besoin de notification pour ça
        ]);

        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * Répondre à un message (depuis le Groupe)
     */
    public function replyMessage(Request $request, $id)
    {
        $request->validate([
            'reply' => 'required|string|max:1000'
        ]);

        $group = Auth::user();
        
        if ($group->type_role !== 'groupe') {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $message = Message::where('id', $id)->where('group_id', $group->id)->firstOrFail();

        $message->update([
            'group_reply' => $request->reply,
            'is_read_by_visitor' => false, // Notification pour le visiteur
            'is_read_by_group' => true, // Le groupe vient d'écrire
        ]);

        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * Obtenir les messages d'un Visiteur
     */
    public function getVisitorMessages()
    {
        $visitor = Auth::user();

        if ($visitor->type_role !== 'visiteur') {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $messages = Message::with(['group:id,name', 'group.groupProfile:user_id,project_name,project_image'])
                           ->where('visitor_id', $visitor->id)
                           ->orderBy('created_at', 'desc')
                           ->get();

        // Append project_name to each message's group for easier front-end use
        $messages->each(function ($message) {
            if ($message->group && $message->group->groupProfile) {
                $message->group->project_name = $message->group->groupProfile->project_name;
                $message->group->project_image = $message->group->groupProfile->project_image;
            } else {
                $message->group->project_name = $message->group->name ?? 'Groupe';
            }
        });

        return response()->json(['success' => true, 'messages' => $messages]);
    }

    /**
     * Obtenir les messages d'un Groupe
     */
    public function getGroupMessages()
    {
        $group = Auth::user();

        if ($group->type_role !== 'groupe') {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        // On a besoin du profil visiteur pour afficher le nom du visiteur
        $messages = Message::with('visitor.visitorProfile')
                           ->where('group_id', $group->id)
                           ->orderBy('created_at', 'desc')
                           ->get();

        return response()->json(['success' => true, 'messages' => $messages]);
    }

    /**
     * Marquer tous les messages comme lus pour l'utilisateur courant
     */
    public function markAsRead()
    {
        $user = Auth::user();

        if ($user->type_role === 'visiteur') {
            Message::where('visitor_id', $user->id)
                   ->where('is_read_by_visitor', false)
                   ->update(['is_read_by_visitor' => true]);
        } elseif ($user->type_role === 'groupe') {
            Message::where('group_id', $user->id)
                   ->where('is_read_by_group', false)
                   ->update(['is_read_by_group' => true]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Compter le nombre de messages non lus pour l'utilisateur courant
     */
    public function unreadCount()
    {
        $user = Auth::user();
        $count = 0;

        if ($user) {
            if ($user->type_role === 'visiteur') {
                $count = Message::where('visitor_id', $user->id)
                               ->where('is_read_by_visitor', false)
                               ->count();
            } elseif ($user->type_role === 'groupe') {
                $count = Message::where('group_id', $user->id)
                               ->where('is_read_by_group', false)
                               ->count();
            }
        }

        return response()->json(['success' => true, 'unread_count' => $count]);
    }

    /**
     * Supprimer tous les messages pour l'utilisateur courant
     */
    public function clearMessages()
    {
        $user = Auth::user();

        if ($user->type_role === 'visiteur') {
            Message::where('visitor_id', $user->id)->delete();
        } elseif ($user->type_role === 'groupe') {
            Message::where('group_id', $user->id)->delete();
        }

        return response()->json(['success' => true]);
    }
}
