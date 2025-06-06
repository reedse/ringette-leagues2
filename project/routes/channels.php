<?php

use App\Models\User;
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

// Private channel for individual user notifications
Broadcast::channel('user.{userId}', function (User $user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Private channel for team notifications - only accessible to team members
Broadcast::channel('team.{teamId}', function (User $user, $teamId) {
    return $user->player && $user->player->teams()->where('teams.id', $teamId)->exists();
}); 