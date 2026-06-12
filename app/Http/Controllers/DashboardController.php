<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $manager = $request->user();

        $projects = Project::where('created_by', $manager->id)->count();

        $tasks = Task::whereHas('project', fn($q) => 
            $q->where('created_by', $manager->id)
        )->count();

        $completed = Task::where('status', 'completed')
            ->whereHas('project', fn($q) => 
                $q->where('created_by', $manager->id)
            )->count();

        return response()->json([
            'projects' => $projects,
            'tasks' => $tasks,
            'completed_tasks' => $completed,
            'progress' => $tasks ? round(($completed / $tasks) * 100) : 0
        ]);
    }

    public function userProgress(Request $request)
    {
        $user = $request->user();

        $total = $user->tasks()->count();
        $completed = $user->tasks()->where('status', 'completed')->count();

        return response()->json([
            'progress' => $total ? round(($completed / $total) * 100) : 0
        ]);
    }
}

