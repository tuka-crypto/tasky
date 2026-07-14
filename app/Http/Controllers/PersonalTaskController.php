<?php

namespace App\Http\Controllers;

use App\Http\Requests\PersonalTaskStoreRequest;
use App\Http\Requests\PersonalTaskUpdateRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\TaskHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PersonalTaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = Task::where('created_by', $request->user()->id)
            ->whereNull('project_id')
            ->latest()
            ->get();

        return TaskResource::collection($tasks);
    }

    public function store(PersonalTaskStoreRequest $request)
    {
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'project_id' => null,
            'category_id' => null,
            'created_by' => $request->user()->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'priority' => $request->priority,
            'status' => 'todo',
            'is_approved' => true,
        ]);

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'action' => 'Personal task created',
        ]);

        return new TaskResource($task);
    }

    public function show(Task $task)
    {
        Gate::authorize('viewPersonal', $task);

        return new TaskResource($task);
    }

    public function update(PersonalTaskUpdateRequest $request, Task $task)
    {
        Gate::authorize('updatePersonal', $task);

        $task->update($request->validated());

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'action' => 'Personal task updated',
        ]);

        return new TaskResource($task);
    }

    public function destroy(Task $task)
    {
        Gate::authorize('deletePersonal', $task);

        $task->history()->delete();
        $task->delete();

        return response()->json([
            'message' => 'Personal task deleted successfully'
        ]);
    }
    public function updateStatus(Request $request, Task $task)
{
    Gate::authorize('updatePersonal', $task);

    $request->validate([
        'status' => 'required|in:todo,in_progress,review,done',
    ]);

    $task->update([
        'status' => $request->status,
    ]);

    TaskHistory::create([
        'task_id' => $task->id,
        'user_id' => $request->user()->id,
        'action' => "Personal task status changed to {$request->status}",
    ]);

    return response()->json([
        'message' => 'Status updated successfully',
        'data' => new TaskResource($task),
    ]);
}
public function filter(Request $request)
{
    $request->validate([
        'status' => 'required|in:todo,in_progress,done'
    ]);
    $tasks = Task::where('created_by', $request->user()->id)
        ->whereNull('project_id');

    if ($request->filled('status')) {
        $tasks->where('status', $request->status);
    }

    if ($request->filled('priority')) {
        $tasks->where('priority', $request->priority);
    }

    return TaskResource::collection(
        $tasks->latest()->get()
    );
}
public function count()
{
    $count = Task::where('user_id', Auth::id())
        ->whereNull('project_id')
        ->count();

    return response()->json([
        'count' => $count
    ]);
}
}