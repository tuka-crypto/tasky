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
    return $user->isManager()
        &&$team->projects()
    ->where('created_by',$user->id)
    ->exists();
}
    public function create(User $user)
    {
        return $user->isManager();
    }
    public function view(User $user, Team $team)
{
    if ($user->isManager()) {
        return  $team->projects()
    ->where('created_by',$user->id)
    ->exists();
    }

    if ($user->isMember()) {
        return $team->members()
                    ->whereKey($user->id)
                    ->exists();
    }

    return false;
}
}
