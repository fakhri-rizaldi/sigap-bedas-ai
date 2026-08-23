<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Channel privat per dinas (khusus staf dinas terkait)
Broadcast::channel('dinas.{dinasId}', function ($user, $dinasId) {
    return (int) $user->dinas_id === (int) $dinasId || $user->is_admin;
});
