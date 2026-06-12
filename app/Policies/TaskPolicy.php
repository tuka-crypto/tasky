<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task)
    {
        if ($user->isManager()) {
            return $task->project->created_by === $user->id;
        }
        if ($user->isMember()) {
            return $task->members->contains($user->id);
        }
        return false;
    }
    /**
     * Create task inside project
     */
    public function create(User $user, $project)
    {
        return $user->isManager() && $project->created_by === $user->id;
    }
    /**
     * Update task
     */
    public function update(User $user, Task $task)
    {
        return $user->isManager() && $task->project->created_by === $user->id;
    }
    /**
     * Delete task
     */
    public function delete(User $user, Task $task)
    {
        return $user->isManager() && $task->project->created_by === $user->id;
    }
    /**
     * Assign members
     */
    public function assignMembers(User $user, Task $task)
    {
        return $user->isManager() && $task->project->created_by === $user->id;
    }
    /**
     * Update status (member only)
     */
    public function updateStatus(User $user, Task $task)
    {
        return $task->members->contains($user->id);
    }
    /**
     * Attach file (manager or assigned member)
     */
    public function attachFile(User $user, Task $task)
    {
        return $task->members->contains($user->id)
            || $task->project->created_by === $user->id;
    }
    /**
     * Add tag
     */
    public function addTag(User $user, Task $task)
    {
        return $user->isManager() && $task->project->created_by === $user->id;
    }
    /**
     * Add dependency
     */
    public function addDependency(User $user, Task $task)
    {
        return $user->isManager() && $task->project->created_by === $user->id;
    }
}

