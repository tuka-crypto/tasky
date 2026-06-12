<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectstoreRequest;
use App\Http\Requests\ProjectupdateRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class Projectcontroller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => __('message.unauthorized')], 403);
        }
        $projects = Project::with('teams')->latest()->get();
        return response()->json([
            'status' => 'success',
            'data'   => ProjectResource::collection($projects)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectstoreRequest $request)
    {
        Gate::authorize('create', Project::class);
            $project = Project::create([
            'title'       => $request->title,
            'description' => $request->description,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'created_by'  => $request->user()->id,
        ]);
        // Attach teams (one or more)
        if ($request->teams) {
            $project->teams()->sync($request->teams);
        }

        return response()->json([
            'status'  => 'success',
            'message' => __('message.project_created'),
            'data'    => new ProjectResource($project)
        ], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $project = Project::with('teams')->find($id);
        if (!$project) {
            return response()->json(['message' => __('message.project_not_found')], 404);
        }
        return response()->json([
            'status' => 'success',
            'data'   => new ProjectResource($project)
        ]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectupdateRequest $request, Project $project)
    {
        Gate::authorize('update', Project::class);
        if ($project->created_by !== $request->user()->id) {
        return response()->json(['message' => __('message.unauthorized')], 403);
    }
        $project->update($request->validated());
        if ($request->teams) {
            $project->teams()->sync($request->teams);
        }
        return response()->json([
            'status'  => 'success',
            'message' => __('message.project_updated'),
            'data'    => new ProjectResource($project)
        ]);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project,Request $request)
    {
        Gate::authorize('delete', Project::class);
        if ($project->created_by !== $request->user()->id) {
        return response()->json(['message' => __('message.unauthorized')], 403);
    }
        $project->teams()->detach();
        $project->delete();
        return response()->json([
            'status'  => 'success',
            'message' => __('message.project_deleted')
        ]);
    }
    public function myprojects(Request $request){
        if (!$request->user()->isManager()) {
            return response()->json(['message' => __('message.unauthorized')], 403);
        }
        $projects = Project::with('teams')
        ->where('created_by', $request->user()->id)
        ->latest()
        ->get();
        return response()->json([
            'status' => 'success',
            'data'   => ProjectResource::collection($projects)
        ]);
    }
    public function search(Request $request)
{
    if (!$request->user()->isManager()||!$request->user()->isAdmin()) {
        return response()->json(['message' => __('message.unauthorized')], 403);
    }
    $query = $request->input('query');
    $projects = Project::with('teams')
        ->where('created_by', $request->user()->id)
        ->where(function ($q) use ($query) {
            $q->where('title', 'like', "%$query%")
            ->orWhere('description', 'like', "%$query%");
        })
        ->latest()
        ->get();

    return response()->json([
        'status' => 'success',
        'data'   => ProjectResource::collection($projects)
    ]);
}

    public function projectsCount(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => __('message.unauthorized')], 403);
        }
        $count = Project::count();
        return response()->json([
            'status'  => 'success',
            'message' => __('message.num_project'),
            'count'   => $count
        ]);
}
    public function myteams(Request $request)
{
    if (!$request->user()->isManager()) {
        return response()->json(['message' => __('message.unauthorized')], 403);
    }

    $teams = Team::whereHas('projects', function ($q) use ($request) {
        $q->where('created_by', $request->user()->id);
    })->with('projects')->get();

    return response()->json([
        'status' => 'success',
        'data'   => $teams
    ]);
}
}