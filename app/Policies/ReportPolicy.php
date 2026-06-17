<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    public function viewUserReport(User $user, User $target)
    {
        return $user->isAdmin()
            || $user->id == $target->id
            || ($user->isManager() && $target->manager_id == $user->id);
    }

    public function viewAllPerformance(User $user)
    {
        return $user->isAdmin();
    }
}
