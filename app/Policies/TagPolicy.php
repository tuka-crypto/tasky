<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
    public function create(User $user)
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function delete(User $user, Tag $tag)
    {
        return $user->isAdmin();
    }
}
