<?php

namespace App\Policies;

use App\Models\Project;
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
    public function view(User $user, Project $project)
{
    if ($user->isAdmin()) {
        return true;
    }

    if ($user->isManager()) {
        return $project->created_by == $user->id;
    }

    return $project->teams()
        ->whereHas('members',function($q) use($user){
            $q->where('users.id',$user->id)
            ->wherePivot('status','accepted');
        })->exists();
}
}
