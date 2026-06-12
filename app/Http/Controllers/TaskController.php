<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignMembersRequest;
use App\Http\Requests\attachfileRequest;
use App\Http\Requests\dependencyrequest;
use App\Http\Requests\TagRequest;
use App\Http\Requests\TaskstoreRequest;
use App\Http\Requests\TasksupdateRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Reward;
use App\Models\Tag;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskDependency;
use App\Models\TaskHistory;
use App\Models\User;
use App\Models\UserPerformance;
use App\Services\FcmServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{/*
display all tasks for manager of his projects
*/
    public function index(Request $request, Project $project)
    {
        Gate::authorize('create', [Task::class, $project]);
        $tasks = Task::where('project_id', $project->id)
            ->with(['members', 'tags', 'attachments', 'dependencies', 'history'])
            ->get();
        return TaskResource::collection($tasks);
    }
    /**
     * Member: list his tasks
     */
    public function myTasks(Request $request)
    {
        $tasks = $request->user()->tasks()
            ->with(['project', 'tags', 'attachments'])
            ->get();
        return TaskResource::collection($tasks);
    }
    /**
     * Show task details
     */
    public function show(Request $request, Task $task)
    {
        Gate::authorize('view', $task);
        return new TaskResource(
            $task->load(['members', 'tags', 'attachments', 'dependencies', 'history', 'project'])
        );
    }
    /**
     * Create task (Manager only)
     */
    public function store(TaskstoreRequest $request, Project $project)
    {
        Gate::authorize('create', [Task::class, $project]);
        $task = Task::create([
            'title'       => $request->title,
            'description' => $request->description,
            'project_id'  => $project->id,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'status'      => 'pending',
            'priority'    => $request->priority,
            'category_id' => $request->category_id,
        ]);
        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'action'  => 'Task created'
        ]);
        return new TaskResource($task);
    }
    /**
     * Update task (Manager only)
     */
    public function update(TasksupdateRequest $request, Task $task)
    {
        Gate::authorize('update', $task);
        $task->update($request->validated());
        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'action'  => 'Task updated'
        ]);
        return new TaskResource($task);
    }
    /**
     * Delete task (Manager only)
     */
    public function destroy(Request $request, Task $task)
    {
        Gate::authorize('delete', $task);
        foreach ($task->attachments as $att) {
            Storage::disk('public')->delete($att->file_path);
        }
        $task->members()->detach();
        $task->attachments()->delete();
        $task->tags()->detach();
        $task->dependencies()->delete();
        $task->history()->delete();
        $task->delete();
        return response()->json(['message' => __('message.task_deleted')]);
    }
    /**
     * Assign members to task (Manager only)
     * Condition: member must have accepted invitation to project
     */
    public function assignMembers(AssignMembersRequest $request, Task $task)
    {
        Gate::authorize('assignMembers', $task);
        $project = $task->project;
        $validMembers = [];

        foreach ($request->members as $memberId) {
            $member = User::find($memberId);

            $isAccepted = $member->teams()
                ->wherePivot('status', 'accepted')
                ->whereHas('projects', function ($q) use ($project) {
                    $q->where('project_id', $project->id);
                })
                ->exists();

            if ($isAccepted) {
                $validMembers[] = $memberId;
            }
        }

        if (empty($validMembers)) {
            return response()->json(['message' => __('message.no_valid_members')], 400);
        }

        $task->members()->syncWithoutDetaching($validMembers);

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'action'  => 'Members assigned'
        ]);

        return response()->json(['message' => __('message.members_assigned')]);
    }

    /**
     * Member updates task status
     */
    public function updateStatus(UpdateStatusRequest $request, Task $task,FcmServices $fcmService)
    {
        Gate::authorize('updateStatus', $task);
        $task->update(['status' => $request->status]);
        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'action'  => "Status changed to {$request->status}"
        ]);
        // Performance + Rewards (مثال بسيط)
        if ($request->status === 'completed') {
            UserPerformance::create([
                'user_id' => $request->user()->id,
                'task_id' => $task->id,
                'score'   => 10, // مثال
            ]);
            Reward::create([
                'user_id'     => $request->user()->id,
                'reward_type' => 'task_completed',
                'points'      => 10,
            ]);
        }
        Notification::create([
        'user_id' => $task->project->created_by,
        'title'   => 'Task status updated',
        'message' => "Task '{$task->title}' status is now {$request->status}",
        'is_read' => false,
    ]);
    $manager = User::find($task->project->created_by);
    $tokens = $manager->notificationTokens()->pluck('token')->toArray();
    $fcmService->sendToUser(
        $tokens,
        'Task status updated',
        "Task '{$task->title}' status is now {$request->status}",
        ['task_id' => $task->id]
    );
        return new TaskResource($task);
    }

    /**
     * Attach file to task (member or manager assigned to task)
     */
    public function attachFile(attachfileRequest $request, Task $task)
    {
        Gate::authorize('attachFile', $task);
        $path = $request->file('file')->store('task_attachments', 'public');

        TaskAttachment::create([
            'task_id'   => $task->id,
            'file_name' => $request->file('file')->getClientOriginalName(),
            'file_path' => $path,
        ]);
        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'action'  => 'File attached'
        ]);
        return response()->json(['message' => __('message.file_attached')]);
    }
    /**
     * Add tag to task (Manager only)
     */
    public function addTag(TagRequest $request, Task $task)
    {
        Gate::authorize('addTag', $task);
        $tag = Tag::firstOrCreate(['name' => $request->name]);
        $task->tags()->syncWithoutDetaching([$tag->id]);
        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'action'  => 'Tag added'
        ]);
        return response()->json(['message' => __('message.tag_added')]);
    }
    /**
     * Add dependency to task (Manager only)
     */
    public function addDependency(dependencyrequest $request, Task $task)
    {
        Gate::authorize('addDependency', $task);
        TaskDependency::create([
            'task_id'           => $task->id,
            'depends_on_task_id'=> $request->depends_on_task_id,
        ]);
        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'action'  => 'Dependency added'
        ]);
        return response()->json(['message' => __('message.dependency_added')]);
    }
}

