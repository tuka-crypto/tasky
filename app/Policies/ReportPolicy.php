<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ReportPolicy
{
public function viewUserReport(User $user, User $target)
{
    if ($user->isAdmin()) {
        return true;
    }

    if ($user->id === $target->id) {
        return true;
    }

    if ($user->isManager()) {

        return Project::where('created_by', $user->id)
            ->whereHas('teams.members', function ($query) use ($target) {
                $query->where('users.id', $target->id);
            })
            ->exists();
    }

    return false;
}

    public function viewAllPerformance(User $user)
    {
        return $user->isAdmin();
    }
}
