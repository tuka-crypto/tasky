<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RewardController extends Controller
{
    /**
     * Admin or Manager: list all rewards for all users
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Reward::class);

        $rewards = Reward::with('user:id,first_name,last_name')->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $rewards,
        ]);
    }

    /**
     * Admin or Manager: create reward for a user
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Reward::class);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'reward_amount' => 'required|numeric|min:0',
            'reward_level' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $reward = Reward::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => __('message.reward_created'),
            'data' => $reward,
        ], 201);
    }

    /**
     * Member: view his own rewards
     */
    public function myReward(Request $request)
    {
        $user = $request->user();

        $rewards = Reward::where('user_id', $user->id)->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $rewards,
        ]);
    }
}
