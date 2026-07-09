<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Create a new policy instance.
     */
    public function manage(User $user, Team $team)
    {
        return $user->isManager() && $team->created_by === $user->id;
    }

    public function create(User $user)
    {
        return $user->isManager();
    }
    public function view(User $user, Team $team)
    {
        if ($user->isManager()) {
            return $team->created_by === $user->id;
        }

        if ($user->isMember()) {
            return $team->members->contains($user->id);
        }

        return false;
    }
}
