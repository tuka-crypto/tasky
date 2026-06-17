<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function create(User $user)
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function update(User $user, Category $category)
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function delete(User $user, Category $category)
    {
        return $user->isAdmin();
    }
}
