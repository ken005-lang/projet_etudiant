<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TabSessionController extends Controller
{
    /**
     * Reçoit le "beacon" du navigateur indiquant que l'onglet est en train d'être fermé.
     * On marque l'heure exacte de cette fermeture supposée dans la session.
     */
    public function beacon(Request $request)
    {
        if (auth()->check()) {
            $request->session()->put('tab_closed_at', now()->timestamp);
            $request->session()->save(); // Force la sauvegarde immédiate de la session
        }
        
        // Retourner 204 No Content, car le sendBeacon ne lit pas la réponse de toute façon
        return response('', 204);
    }
}
