<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignMembersRequest;
use App\Http\Requests\attachfileRequest;
use App\Http\Requests\dependencyrequest;
use App\Http\Requests\TaskstoreRequest;
use App\Http\Requests\TasksupdateRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Reward;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskDependency;
use App\Models\TaskHistory;
use App\Models\User;
use App\Models\UserPerformance;
use App\Services\FcmServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    /*
    Display all tasks for manager of his project
    */
    public function index(Request $request, Project $project)
    {
        Gate::authorize('viewAny', [Task::class, $project]);

        $tasks = Task::where('project_id', $project->id)
            ->with(['members', 'attachments', 'dependencies', 'history'])
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
            $task->load(['members', 'attachments', 'dependencies', 'history', 'project'])
        );
    }

    /**
     * Create task (Manager only)
     */
    public function store(TaskstoreRequest $request, Project $project)
    {
        Gate::authorize('create', [Task::class, $project]);

        $task = Task::create([
'title'=>$request->title,
'description'=>$request->description,
'project_id'=>$project->id,
'start_date'=>$request->start_date,
'end_date'=>$request->end_date,
'priority'=>$request->priority,
'status'=>'pending',
'category_id'=>$request->category_id,
'created_by'=>$request->user()->id,
]);

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'action' => 'Task created',
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
            'action' => 'Task updated',
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
            Storage::disk('public')->delete($att->file_path ?? '');
        }

        $task->members()->detach();
        $task->attachments()->delete();
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
            if(!$member){
                continue;
                }
            $isAccepted = $member->teams()
                ->wherePivot('status', 'accepted')
                ->whereHas('projects', function ($q) use ($project) {
                    $q->where('projects.id', $project->id);
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
            'action' => 'Members assigned',
        ]);

        return response()->json(['message' => __('message.members_assigned')]);
    }

    /**
     * Member updates task status
     */
    public function updateStatus(UpdateStatusRequest $request, Task $task, FcmServices $fcmService)
    {
        Gate::authorize('updateStatus', $task);

        $task->update(['status' => $request->status]);

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'action' => "Status changed to {$request->status}",
        ]);

        // Performance + Rewards
        if ($request->status === 'completed') {
            UserPerformance::create([
                'user_id' => $request->user()->id,
                'task_id' => $task->id,
                'score' => 10,
            ]);

            Reward::create([
                'user_id' => $request->user()->id,
                'reward_type' => 'task_completed',
                'points' => 10,
            ]);
        }

        // Notification
        Notification::create([
            'user_id' => $task->project->created_by,
            'title' => 'Task status updated',
            'message' => "Task '{$task->title}' status is now {$request->status}",
            'is_read' => false,
        ]);

        // FCM
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
     * Attach file to task
     */
    public function attachFile(attachfileRequest $request, Task $task)
    {
        Gate::authorize('attachFile', $task);

        $path = $request->file('file')->store('task_attachments', 'public');

        TaskAttachment::create([
'task_id'=>$task->id,
'uploaded_by'=>$request->user()->id,
'file_name'=>$request->file('file')->getClientOriginalName(),
'file_path'=>$path,
'file_type'=>$request->file('file')->getMimeType(),
'file_size'=>$request->file('file')->getSize(),
]);

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'action' => 'File attached',
        ]);

        return response()->json(['message' => __('message.file_attached')]);
    }

    /**
     * Remove attachment
     */
    public function removeAttachment(Request $request, Task $task, TaskAttachment $attachment)
    {
        Gate::authorize('attachFile', $task);

        if ($attachment->task_id !== $task->id) {
            return response()->json(['message' => __('message.unauthorized')], 403);
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'action' => 'Attachment removed',
        ]);

        return response()->json(['message' => __('message.file_removed')]);
    }

    /**
     * Add dependency
     */
    public function addDependency(dependencyrequest $request, Task $task)
    {
        Gate::authorize('addDependency', $task);
        $depends=Task::findOrFail($request->depends_on_task_id);

        if($depends->project_id!=$task->project_id){
            return response()->json(['message' => __('message.invalid_dependency')], 400);
        }
        if ($request->depends_on_task_id == $task->id) {
            return response()->json(['message' => __('message.invalid_dependency')], 400);
        }

        TaskDependency::create([
            'task_id' => $task->id,
            'depends_on_task_id' => $request->depends_on_task_id,
        ]);

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'action' => 'Dependency added',
        ]);

        return response()->json(['message' => __('message.dependency_added')]);
    }

    /**
     * Remove dependency
     */
    public function removeDependency(Request $request, Task $task, TaskDependency $dependency)
    {
        Gate::authorize('addDependency', $task);

        if ($dependency->task_id !== $task->id) {
            return response()->json(['message' => __('message.unauthorized')], 403);
        }

        $dependency->delete();

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'action' => 'Dependency removed',
        ]);

        return response()->json(['message' => __('message.dependency_removed')]);
    }
    /**
     * Count tasks in project
     */
    public function tasksCount(Project $project)
    {
        Gate::authorize('viewAny', [Task::class, $project]);

        $count = Task::where('project_id', $project->id)->count();

        return response()->json(['count' => $count]);
    }
//manager show all completed tasks in his project
    public function completedTasks(Request $request)
    {
    $manager = $request->user();
    if(!$manager->isManager()){
    return response()->json([
        'message'=>__('message.unauthorized')
    ],403);
}
    $tasks = Task::where('status', 'completed')
                ->whereHas('project', function ($q) use ($manager) {
                    $q->where('created_by', $manager->id);
                })
                ->with(['project', 'members'])
                ->latest()
                ->get();

    return TaskResource::collection($tasks);
}
//manager accept task
    public function approvedTasks(Task $task){
    Gate::authorize('isApproved', $task);
    $task->update([
        'is_approved' => true
    ]);
    TaskHistory::create([
        'task_id'=>$task->id,
        'user_id'=>Auth::user()->id,
        'action'=>'Task approved'
]);
    return response()->json(['message' => __('message.accept_task')]);
    }
//manager rejected task and task comeback inprogress
    public function rejectTasks(Task $task){
    Gate::authorize('isApproved', $task);
    $task->update([
        'is_approved' => false,
        'status'      => 'in_progress'
    ]);
    TaskHistory::create([
        'task_id'=>$task->id,
        'user_id'=>Auth::user()->id,
        'action'=>'Task rejected'
]);
    return response()->json(['message' => __('message.reject_task')]);
    }
}
