<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal privé pour un groupe spécifique
Broadcast::channel('group.messages.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id && $user->type_role === 'groupe';
});

// Canal privé pour un visiteur spécifique
Broadcast::channel('visitor.messages.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id && $user->type_role === 'visiteur';
});
