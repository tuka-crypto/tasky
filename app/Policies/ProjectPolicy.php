<?php

namespace App\Policies;

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
        public function update(User $user): bool
        {
            return $user->role_id === 2; // Only manager can update projects
        }
        public function delete(User $user): bool
        {
            return $user->role_id === 2; // Only manager can delete projects
        }
}
