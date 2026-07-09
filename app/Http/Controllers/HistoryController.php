<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectHistory;
use App\Models\Task;
use App\Models\TaskHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class HistoryController extends Controller
{
    /**
     * List all project logs for a specific project
     */
    public function projectIndex(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $logs = ProjectHistory::where('project_id', $project->id)
            ->with('user:id,first_name,last_name')
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $logs,
        ]);
    }

    /**
     * Show single project log entry
     */
    public function projectLog(Request $request, ProjectHistory $log)
    {
        Gate::authorize('view', $log->project);

        return response()->json([
            'status' => 'success',
            'data' => $log->load('user:id,first_name,last_name'),
        ]);
    }

    /**
     * List all task logs for a specific task
     */
    public function taskIndex(Request $request, Task $task)
    {
        Gate::authorize('view', $task);

        $logs = TaskHistory::where('task_id', $task->id)
            ->with('user:id,first_name,last_name')
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $logs,
        ]);
    }

    /**
     * Show single task log entry
     */
    public function taskLog(Request $request, TaskHistory $log)
    {
        Gate::authorize('view', $log->task);

        return response()->json([
            'status' => 'success',
            'data' => $log->load('user:id,first_name,last_name'),
        ]);
    }
}
