<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\SigninRequest;
use App\Http\Requests\SignupRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetCodeMail;

class AuthController extends Controller
{
    /**
     * User Signup
     */
    public function signup(SignupRequest $request)
    {
        $profilePath = null;
        try {
            
            if ($request->hasFile('profile_image')) {
                $profilePath = $request->file('profile_image')->storeAs(
                    'profiles',
                    uniqid().'_'.$request->file('profile_image')->getClientOriginalName(),
                    'public'
                );
            }
            $user = User::create([
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'first_name'    => $request->first_name,
                'last_name'     => $request->last_name,
                'profile_image' => $profilePath,
                'gender'        => $request->gender,
                'role_id'          => $request->role_id,
                'date_of_birth' => $request->date_of_birth,
                'is_approved'   => false,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => __('messages.register_success'),
                'data'    => new UserResource($user)
            ], 201);

        } catch (\Exception $e) {
            if ($profilePath) Storage::disk('public')->delete($profilePath);
            Log::error($e);

            return response()->json([
                'status'  => 'error',
                'message' => __('messages.error')
            ], 500);
        }
    }

    /**
     * Admin Login
     */
    public function adminLogin(AdminloginRequest $request)
    {
        try {
            $user = User::where('email', $request->email)
                        ->where('role_id', 1)
                        ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => __('messages.login_failed')], 401);
            }

            $user->tokens()->delete();
            $token = $user->createToken('admin_token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user'  => new UserResource($user),
            ]);

        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => __('messages.error')], 500);
        }
    }

    /**
     * User Login
     */
    public function signin(SigninRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => __('messages.login_failed')], 401);
            }

            if (!$user->is_approved) {
                return response()->json(['message' => __('messages.pending_approval')], 403);
            }

            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user'  => new UserResource($user),
            ]);

        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => __('messages.error')], 500);
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        try {
            if ($request->user()->currentAccessToken()) {
                $request->user()->currentAccessToken()->delete();
            }

            return response()->json(['message' => __('messages.logout_success')]);

        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => __('messages.error')], 500);
        }
    }

    /**
     * Send Reset Code
     */
    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => __('messages.user_not_found')], 404);
        }

        $code = rand(100000, 999999);

        $user->update([
            'reset_code'       => $code,
            'reset_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new ResetCodeMail($code));

        return response()->json(['message' => __('messages.reset_code_sent')]);
    }

    /**
     * Verify Reset Code
     */
    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email'      => 'required|email',
            'reset_code' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => __('messages.user_not_found')], 404);
        }

        if ($user->reset_code !== $request->reset_code) {
            return response()->json(['message' => __('messages.invalid_code')], 401);
        }

        if (now()->greaterThan($user->reset_expires_at)) {
            return response()->json(['message' => __('messages.code_expired')], 403);
        }

        return response()->json(['message' => __('messages.code_valid')]);
    }

    /**
     * Reset Password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'        => 'required|email',
            'reset_code'   => 'required',
            'new_password' => 'required|min:6'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => __('messages.user_not_found')], 404);
        }

        if ($user->reset_code !== $request->reset_code) {
            return response()->json(['message' => __('messages.invalid_code')], 401);
        }

        if (now()->greaterThan($user->reset_expires_at)) {
            return response()->json(['message' => __('messages.code_expired')], 403);
        }

        $user->update([
            'password'         => Hash::make($request->new_password),
            'reset_code'       => null,
            'reset_expires_at' => null,
        ]);

        return response()->json(['message' => __('messages.password_changed')]);
    }
    /*
    * Get Count of Non-Admin Pending Users
    */
    public function usersCount(Request $request)
{
    if (!$request->user()->isAdmin()) {
        return response()->json(['message' =>__('messages.unauthorize')], 403);
    }
    $count = User::where('role_id', '!=', 1)->where('is_approved',false)->count();
    return response()->json([
        'status'  => 'success',
        'message' =>__('messages.num_user'),
        'count'   => $count
    ]);
}

    /**
     * List Pending Users
     */
    public function pendingUsers(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => __('messages.unauthorize')], 403);
        }

        $users = User::where('is_approved', false)->get();

        return response()->json([
            'status'  => 'success',
            'data'    => UserResource::collection($users),
            'message' => __('messages.pending')
        ]);
    }

    /**
     * Approve User
     */
    public function approveUser(Request $request, User $user)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => __('messages.unauthorize')], 403);
        }

        $user->update(['is_approved' => true]);

        return response()->json([
            'status'  => 'success',
            'message' => __('messages.approve'),
            'data'    => new UserResource($user)
        ]);
    }

    /**
     * Reject User
     */
    public function rejectUser(Request $request, User $user)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => __('messages.unauthorize')], 403);
        }

        $user->update([
            'is_approved' => false,
            'reset_code' => null,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => __('messages.reject'),
            'data'    => new UserResource($user)
        ]);
    }

    /**
     * Delete User
     */
    public function deleteUser(Request $request, User $user)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => __('messages.unauthorize')], 403);
        }

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->delete();

        return response()->json([
            'status'  => 'success',
            'message' => __('messages.delete')
        ]);
    }
}
