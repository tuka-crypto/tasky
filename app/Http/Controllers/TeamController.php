<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeamStoreRequest;
use App\Http\Requests\TeamUpdateRequest;
use App\Http\Requests\UseraddRequest;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use App\Models\User;
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

        if (! $user->isAdmin()) {
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
        Gate::authorize('create', Team::class);
        $team = Team::create([
            'name' => $request->name,
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
        Gate::authorize('view', $team);

        return new TeamResource($team->load(['members' => function ($q) {
            $q->wherePivot('status', 'accepted');
        }]));
    }

    /**
     * Update team
     */
    public function update(TeamUpdateRequest $request, Team $team)
    {
        Gate::authorize('manage', $team);
        $team->update($request->validated());

        return new TeamResource($team);
    }

    /**
     * Delete team
     */
    public function destroy(Request $request, Team $team)
    {
        Gate::authorize('manage', $team);
        $team->members()->detach();
        $team->delete();

        return response()->json(['message' => __('message.team_deleted')]);
    }

    /**
     * Manager sends invitation to member
     */
public function addMember(UseraddRequest $request, Team $team)
{
    // السماح فقط لمدير هذا الفريق
    Gate::authorize('manage', $team);

    // التأكد أن المستخدم موجود
    $user = User::findOrFail($request->user_id);

    // التأكد أن المستخدم Member فقط
    if (! $user->isMember()) {
        return response()->json([
            'message' => __('message.invalid_member')
        ], 403);
    }

    // منع إرسال دعوة لنفس المستخدم أكثر من مرة
    if ($team->members()->where('user_id', $user->id)->exists()) {
        return response()->json([
            'message' => __('message.already_invited')
        ], 409);
    }

    // إرسال الدعوة
    $team->members()->attach($user->id, [
        'status' => 'pending'
    ]);

    return response()->json([
        'status'  => 'success',
        'message' => __('message.invitation_sent')
    ], 201);
}

    // all invitation
    public function allInvitation(Request $request)
    {
        $user = $request->user();

        if ($user->isManager()) {
            // Invitations sent by manager
            $teams = Team::where('created_by', $user->id)
                ->with(['members' => function ($q) {
                    $q->wherePivot('status', 'pending');
                }])->get();

            return response()->json(['data' => $teams]);
        }

        // Invitations received by member
        $invitations = $user->teams()
            ->wherePivot('status', 'pending')
            ->get();

        return response()->json(['data' => $invitations]);
    }

    /**
     * Member accepts invitation
     */
    public function acceptInvitation(Request $request, Team $team)
    {
        $user = $request->user();

        if (! $team->members()->where('user_id', $user->id)->wherePivot('status', 'pending')->exists()) {
            return response()->json(['message' => __('message.unauthorized')], 403);
        }

        $team->members()->updateExistingPivot($user->id, [
            'status' => 'accepted',
        ]);

        return response()->json(['message' => __('message.invitation_accepted')]);
    }

    public function rejectInvitation(Request $request, Team $team)
    {
        $user = $request->user();

        if (! $team->members()->where('user_id', $user->id)->wherePivot('status', 'pending')->exists()) {
            return response()->json(['message' => __('message.unauthorized')], 403);
        }

        $team->members()->detach($user->id);

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
        if ($request->user_id == $team->created_by) {
            return response()->json(['message' => __('message.cannot_remove_manager')], 403);
        }
        $team->members()->detach($request->user_id);

        return response()->json(['message' => __('message.member_removed')]);
    }
}
