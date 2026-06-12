<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Policies\ProjectPolicy;
use App\Policies\TaskPolicy;
use App\Policies\TeamPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Project::class=> ProjectPolicy::class,
        Team::class=>TeamPolicy::class,
        Task::class=> TaskPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}