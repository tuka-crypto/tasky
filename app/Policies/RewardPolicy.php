<?php

namespace App\Policies;

use App\Models\Reward;
use App\Models\User;

class RewardPolicy
{
    public function viewAny(User $user)
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function create(User $user)
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function view(User $user, Reward $reward)
    {
        return $user->isAdmin()
            || $user->isManager()
            || $reward->user_id == $user->id;
    }
}
