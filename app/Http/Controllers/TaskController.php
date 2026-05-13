<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTaskRequest;
use App\Models\Task;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateStatusTaskRequest;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    // Admin: show all tasks
    public function index()
    {
        Gate::authorize('viewAny', Task::class);
        $tasks = Task::all();
        return TaskResource::collection($tasks);
    }
    // Admin: create task
    public function store(CreateTaskRequest $request)
    {
        Gate::authorize('create', Task::class);
        $task = Task::create([
            'title'       => $request->title,
            'description' => $request->description,
            'priority'    => $request->priority,
            'deadline'    => $request->deadline,
            'assigned_to' => $request->assigned_to,
            'created_by'  => $request->user()->id,
        ]);
        return new TaskResource($task);
    }
    // Admin or Member: show one task
    public function show(Task $task)
    {
        Gate::authorize('view', $task);
        return new TaskResource($task);
    }
    // Admin: update task
    public function update(UpdateTaskRequest $request, Task $task)
    {
        Gate::authorize('update', $task);
        $task->update($request->validated());
        return new TaskResource($task);
    }
    // Admin: delete task
    public function destroy(Task $task)
    {
        Gate::authorize('delete', $task);
        $task->delete();
        return response()->json([
            'status'  => 'success',
            'message' => 'Task deleted successfully',
        ]);
    }
    // Member: show all his tasks
    public function myTasks(Request $request)
    {
        $tasks = Task::where('assigned_to', $request->user()->id)->get();
        return TaskResource::collection($tasks);
    }
    // Member: show one of his tasks
    public function myTask(Request $request, Task $task)
    {
        Gate::authorize('view', $task);
        return new TaskResource($task);
    }
    // Member: update status only
    public function updateStatus(UpdateStatusTaskRequest $request, Task $task)
    {
        Gate::authorize('updateStatus', $task);
        $task->update(['status' => $request->status]);
        return new TaskResource($task);
    }
}
