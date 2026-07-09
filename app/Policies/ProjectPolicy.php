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
            return $user->role_id === 2; // Only manager can create projects
        }
        public function update(User $user, Project $project)
        {
    return $user->id == $project->created_by;
        }
        public function delete(User $user, Project $project)
        {
    return $user->id == $project->created_by;
        }
}
