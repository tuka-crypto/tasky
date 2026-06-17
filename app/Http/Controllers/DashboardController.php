<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    /**
     * Manager statistics
     */
    public function stats(Request $request)
    {
        $manager = $request->user();

        Gate::authorize('managerStats', $manager);

        $projects = Project::where('created_by', $manager->id)->count();

        $tasks = Task::whereHas('project', fn ($q) => $q->where('created_by', $manager->id)
        )->count();

        $completed = Task::where('status', 'completed')
            ->whereHas('project', fn ($q) => $q->where('created_by', $manager->id)
            )->count();

        return response()->json([
            'projects' => $projects,
            'tasks' => $tasks,
            'completed_tasks' => $completed,
            'progress' => $tasks ? round(($completed / $tasks) * 100) : 0,
        ]);
    }

    /**
     * Admin statistics (global system stats)
     */
    public function adminState(Request $request)
    {
        $admin = $request->user();

        Gate::authorize('adminStats', $admin);

        $totalProjects = Project::count();
        $totalTasks = Task::count();
        $completed = Task::where('status', 'completed')->count();
        $users = User::count();

        return response()->json([
            'total_users' => $users,
            'total_projects' => $totalProjects,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completed,
            'system_progress' => $totalTasks ? round(($completed / $totalTasks) * 100) : 0,
        ]);
    }

    /**
     * User progress (member)
     */
    public function userProgress(Request $request)
    {
        $user = $request->user();

        $total = $user->tasks()->count();
        $completed = $user->tasks()->where('status', 'completed')->count();

        return response()->json([
            'progress' => $total ? round(($completed / $total) * 100) : 0,
        ]);
    }

    /**
     * Task progress inside a project
     */
    public function taskProgress(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $total = Task::where('project_id', $project->id)->count();
        $completed = Task::where('project_id', $project->id)
            ->where('status', 'completed')
            ->count();

        return response()->json([
            'project_id' => $project->id,
            'progress' => $total ? round(($completed / $total) * 100) : 0,
        ]);
    }
}
