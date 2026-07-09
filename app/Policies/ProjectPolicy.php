<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Create a new policy instance.
     */
        public function create(User $user): bool
        {
            return $user->isManager(); // Only manager can create projects
        }
        public function update(User $user, Project $project)
        {
    return $user->id == $project->created_by;
        }
        public function delete(User $user, Project $project)
        {
    return $user->id == $project->created_by;
        }
        public function view(User $user, Project $project)
{
    if ($user->isAdmin()) {
        return true;
    }

    if ($user->isManager()) {
        return $project->created_by == $user->id;
    }

    if ($user->isMember()) {
        return $project->teams()
            ->whereHas('members', function ($q) use ($user) {
                $q->where('users.id', $user->id)
                ->wherePivot('status', 'accepted');
            })
            ->exists();
    }

    return false;
}
}
