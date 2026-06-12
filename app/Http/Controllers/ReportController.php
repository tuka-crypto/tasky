<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\User;
use App\Models\UserPerformance;
use App\Models\Reward;

class ReportsController extends Controller
{
    public function userReport(User $user)
    {
        return response()->json([
            'user' => $user->first_name . ' ' . $user->last_name,
            'tasks' => TaskResource::collection($user->tasks),
            'performance' => UserPerformance::where('user_id', $user->id)->get(),
            'rewards' => Reward::where('user_id', $user->id)->get(),
        ]);
    }

    public function allUsersPerformance()
    {
        $users = User::with('tasks')->get();

        return $users->map(function ($user) {
            $total = $user->tasks->count();
            $completed = $user->tasks->where('status', 'completed')->count();

            return [
                'user' => $user->first_name,
                'progress' => $total ? round(($completed / $total) * 100) : 0
            ];
        });
    }
}
