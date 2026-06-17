<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Reward;
use App\Models\Task;
use App\Models\User;
use App\Models\UserPerformance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    /**
     * Full report for a specific user
     */
    public function userReport(User $user)
    {
        Gate::authorize('viewUserReport', $user);

        return response()->json([
            'user' => $user->first_name.' '.$user->last_name,
            'tasks' => TaskResource::collection($user->tasks()->with('project')->get()),
            'performance' => UserPerformance::where('user_id', $user->id)->get(),
            'rewards' => Reward::where('user_id', $user->id)->get(),
        ]);
    }

    /**
     * Full report for a specific project
     */
    public function projectReport(Project $project)
    {
        Gate::authorize('view', $project);

        $tasks = Task::where('project_id', $project->id)
            ->with(['members', 'tags'])
            ->get();

        $completed = $tasks->where('status', 'completed')->count();
        $total = $tasks->count();

        return response()->json([
            'project' => $project->title,
            'total_tasks' => $total,
            'completed_tasks' => $completed,
            'progress' => $total ? round(($completed / $total) * 100) : 0,
            'tasks' => TaskResource::collection($tasks),
        ]);
    }

    /**
     * Performance report for a specific user
     */
    public function userPerformance(User $user)
    {
        Gate::authorize('viewUserReport', $user);

        $performance = UserPerformance::where('user_id', $user->id)->get();
        $rewards = Reward::where('user_id', $user->id)->get();

        return response()->json([
            'user' => $user->first_name.' '.$user->last_name,
            'performance' => $performance,
            'rewards' => $rewards,
        ]);
    }

    /**
     * Performance report for the logged-in user
     */
    public function myPerformance(Request $request)
    {
        $user = $request->user();

        $performance = UserPerformance::where('user_id', $user->id)->get();
        $rewards = Reward::where('user_id', $user->id)->get();

        return response()->json([
            'user' => $user->first_name.' '.$user->last_name,
            'performance' => $performance,
            'rewards' => $rewards,
        ]);
    }

    /**
     * Performance for all users (Admin only)
     */
    public function allUsersPerformance()
    {
        Gate::authorize('viewAllPerformance', User::class);

        $users = User::with('tasks')->get();

        return $users->map(function ($user) {
            $total = $user->tasks->count();
            $completed = $user->tasks->where('status', 'completed')->count();

            return [
                'user' => $user->first_name.' '.$user->last_name,
                'progress' => $total ? round(($completed / $total) * 100) : 0,
                'tasks' => $total,
                'completed_tasks' => $completed,
            ];
        });
    }
}
