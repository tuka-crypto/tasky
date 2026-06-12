<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeamStoreRequest;
use App\Http\Requests\TeamUpdateRequest;
use App\Http\Requests\UseraddRequest;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeamController extends Controller
{
    /**
     * Manager: list his teams
     */
    public function index(Request $request)
{
    $user = $request->user();

    if (!$user->isAdmin()) {
        return response()->json(['message' => __('message.unauthorized')], 403);
    }

    $teams = Team::with(['members' => function ($q) {
        $q->wherePivot('status', 'accepted');
    }])->get();

    return TeamResource::collection($teams);
}


    /**
     * Create team
     */
    public function store(TeamStoreRequest $request)
    {
        Gate::authorize('manage', Team::class);
        $team = Team::create([
            'name'       => $request->name,
            'created_by' => $request->user()->id,
        ]);
        return new TeamResource($team);
    }

    /**
     * Show team (Manager sees his team, Member sees teams he belongs to)
     */
    public function show(Request $request, Team $team)
    {
        $user = $request->user();
        Gate::authorize('view',Team::class);
        return new TeamResource($team->load(['members' => function ($q) {
            $q->wherePivot('status', 'accepted');
        }]));
    }

    /**
     * Update team
     */
    public function update(TeamUpdateRequest $request, Team $team)
    {
        Gate::authorize('manage',Team::class);
        $team->update($request->validated());
        return new TeamResource($team);
    }

    /**
     * Delete team
     */
    public function destroy(Request $request, Team $team)
    {
        Gate::authorize('manage',Team::class);
        $team->members()->detach();
        $team->delete();

        return response()->json(['message' => __('message.team_deleted')]);
    }

    /**
     * Manager sends invitation to member
     */
    public function addMember(UseraddRequest $request, Team $team)
    {
        if ($team->created_by !== $request->user()->id) {
            return response()->json(['message' => __('message.unauthorized')], 403);
        }

        // Add as pending
        $team->members()->syncWithoutDetaching([
            $request->user_id => ['status' => 'pending']
        ]);

        return response()->json(['message' => __('message.invitation_sent')]);
    }

    /**
     * Member accepts invitation
     */
    public function acceptInvitation(Request $request, Team $team)
    {
        $user = $request->user();

        if (!$team->members->contains($user->id)) {
            return response()->json(['message' => __('message.unauthorized')], 403);
        }

        $team->members()->updateExistingPivot($user->id, [
            'status' => 'accepted'
        ]);

        return response()->json(['message' => __('message.invitation_accepted')]);
    }


    public function rejectInvitation(Request $request, Team $team)
    {
        $user = $request->user();

        if (!$team->members->contains($user->id)) {
            return response()->json(['message' => __('message.unauthorized')], 403);
        }

        $team->members()->updateExistingPivot($user->id, [
            'status' => 'reject'
        ]);

        return response()->json(['message' => __('message.invitation_rejected')]);
    }

    /**
     * Manager removes member
     */
    public function removeMember(Request $request, Team $team)
    {
        if ($team->created_by !== $request->user()->id) {
            return response()->json(['message' => __('message.unauthorized')], 403);
        }

        $team->members()->detach($request->user_id);

        return response()->json(['message' => __('message.member_removed')]);
    }
}
