<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Live session presence channel - allows authenticated users to join
Broadcast::channel('live-session.{pin}', function ($user, $pin) {
    // Allow any authenticated user (student, teacher, or institute) to join
    if ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name ?? 'User',
            'type' => class_basename(get_class($user))
        ];
    }
    return false;
});

// Exam monitoring channel - public channel for real-time exam monitoring
// Using public channel instead of presence to avoid auth issues
Broadcast::channel('exam.{examId}', function ($user, $examId) {
    // Allow any authenticated user to monitor
    return true;
});

// Per-user notification channel — authorize only the matching user. `type` is the
// lowercased model basename (teacher|student|institute|admin) and must match the
// authenticated user resolved by the resolve.multi.guard middleware.
Broadcast::channel('notifications.{type}.{id}', function ($user, $type, $id) {
    if (!$user) {
        return false;
    }
    return strtolower(class_basename(get_class($user))) === strtolower($type)
        && (int) $user->id === (int) $id;
});

// Ticket conversation channel — the ticket owner (sender) or any admin may listen.
Broadcast::channel('ticket.{id}', function ($user, $id) {
    if (!$user) {
        return false;
    }
    if (strtolower(class_basename(get_class($user))) === 'admin') {
        return true;
    }
    $ticket = \App\Models\Ticket::find($id);
    if (!$ticket) {
        return false;
    }
    return (int) $ticket->sender_id === (int) $user->id
        && class_basename((string) $ticket->sender_type) === class_basename(get_class($user));
});
