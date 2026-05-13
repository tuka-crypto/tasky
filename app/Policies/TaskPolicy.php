<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user)
    {
        return $user->isAdmin();
    }
    public function view(User $user, Task $task)
    {
        return $user->isAdmin() || $task->assigned_to === $user->id;
    }
    public function create(User $user)
    {
        return $user->isAdmin();
    }
    public function update(User $user, Task $task)
    {
        return $user->isAdmin();
    }
    public function delete(User $user, Task $task)
    {
        return $user->isAdmin();
    }
    public function updateStatus(User $user, Task $task)
    {
        return $task->assigned_to === $user->id;
    }
}
