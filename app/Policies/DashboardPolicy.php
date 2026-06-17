<?php

namespace App\Policies;

use App\Models\User;

class DashboardPolicy
{
    public function managerStats(User $user)
    {
        return $user->isManager();
    }

    public function adminStats(User $user)
    {
        return $user->isAdmin();
    }
}
