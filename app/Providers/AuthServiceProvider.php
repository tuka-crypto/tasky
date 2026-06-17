<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Project;
use App\Models\Reward;
use App\Models\Tag;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\DashboardPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\ReportPolicy;
use App\Policies\RewardPolicy;
use App\Policies\TagPolicy;
use App\Policies\TaskPolicy;
use App\Policies\TeamPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Project::class => ProjectPolicy::class,
        Team::class => TeamPolicy::class,
        Task::class => TaskPolicy::class,
        Category::class => CategoryPolicy::class,
        Tag::class => TagPolicy::class,
        Reward::class => RewardPolicy::class,
        User::class => ReportPolicy::class,
        User::class => DashboardPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
