<?php

namespace App\Policies;

use App\Models\Clip;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClipPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Coaches can view all clips
        if ($user->isCoach()) {
            return true;
        }
        
        // Players need a subscription to view clips
        if ($user->isPlayer()) {
            return $user->subscribed('clips');
        }
        
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Clip $clip): bool
    {
        // Coaches can view any clip
        if ($user->isCoach()) {
            return true;
        }
        
        // Players need a subscription and the clip must be shared with them
        if ($user->isPlayer() && $user->subscribed('clips')) {
            $player = $user->player;
            
            if (!$player) {
                return false;
            }
            
            return $clip->players()->where('player_id', $player->id)->exists();
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only coaches can create clips
        return $user->isCoach();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Clip $clip): bool
    {
        // Only coaches who created the clip can update it
        return $user->isCoach() && $clip->coach_user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Clip $clip): bool
    {
        // Only coaches who created the clip can delete it
        return $user->isCoach() && $clip->coach_user_id === $user->id;
    }
} 