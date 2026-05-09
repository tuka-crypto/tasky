<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminloginRequest;
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
    // ---------------------------
    // SIGN UP
    // ---------------------------
    public function signup(SignupRequest $request)
    {
        try {
            $profilePath = $request->file('profile_image')->storeAs(
                'profiles',
                uniqid().'_'.$request->file('profile_image')->getClientOriginalName(),
                'public'
            );

            $idCardPath = $request->file('id_card_image')->storeAs(
                'id_cards',
                uniqid().'_'.$request->file('id_card_image')->getClientOriginalName(),
                'public'
            );

            $user = User::create([
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'first_name'    => $request->first_name,
                'last_name'     => $request->last_name,
                'profile_image' => $profilePath,
                'id_card_image' => $idCardPath,
                'gender'        => $request->gender,
                'role'          => $request->role,
                'date_of_birth' => $request->date_of_birth,
                'is_approved'   => false,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => __('messages.register_success'),
                'data'    => new UserResource($user)
            ], 201);

        } catch (\Exception $e) {
            if (isset($profilePath)) Storage::disk('public')->delete($profilePath);
            if (isset($idCardPath)) Storage::disk('public')->delete($idCardPath);

            Log::error($e);

            return response()->json([
                'status'  => 'Error',
                'message' => __('messages.error')
            ], 500);
        }
    }

    // ---------------------------
    // ADMIN LOGIN
    // ---------------------------
    public function adminLogin(AdminloginRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => __('messages.login_failed')], 401);
            }

            if (!$user->isAdmin()) {
                return response()->json(['message' => __('messages.unauthorize')], 403);
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

    // ---------------------------
    // USER LOGIN
    // ---------------------------
    public function signin(SigninRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => __('messages.login_failed')], 401);
            }

            if (!$user->is_approved) {
                return response()->json(['message' => __('messages.otp_pending')], 403);
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

    // ---------------------------
    // LOGOUT
    // ---------------------------
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

    // ---------------------------
    // SEND RESET CODE
    // ---------------------------
    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'المستخدم غير موجود'], 404);
        }

        $code = rand(100000, 999999);

        $user->update([
            'reset_code'       => $code,
            'reset_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new ResetCodeMail($code));

        return response()->json(['message' => 'تم إرسال كود استعادة كلمة المرور']);
    }

    // ---------------------------
    // VERIFY RESET CODE
    // ---------------------------
    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email'      => 'required|email',
            'reset_code' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'المستخدم غير موجود'], 404);
        }

        if ($user->reset_code !== $request->reset_code) {
            return response()->json(['message' => 'الكود غير صحيح'], 401);
        }

        if (now()->greaterThan($user->reset_expires_at)) {
            return response()->json(['message' => 'انتهت صلاحية الكود'], 403);
        }

        return response()->json(['message' => 'الكود صحيح']);
    }

    // ---------------------------
    // RESET PASSWORD
    // ---------------------------
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'        => 'required|email',
            'reset_code'   => 'required',
            'new_password' => 'required|min:6'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'المستخدم غير موجود'], 404);
        }

        if ($user->reset_code !== $request->reset_code) {
            return response()->json(['message' => 'الكود غير صحيح'], 401);
        }

        if (now()->greaterThan($user->reset_expires_at)) {
            return response()->json(['message' => 'انتهت صلاحية الكود'], 403);
        }

        $user->update([
            'password'         => Hash::make($request->new_password),
            'reset_code'       => null,
            'reset_expires_at' => null,
        ]);

        return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح']);
    }

    // ---------------------------
    // PENDING USERS
    // ---------------------------
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

    // ---------------------------
    // APPROVE USER
    // ---------------------------
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

    // ---------------------------
    // REJECT USER
    // ---------------------------
    public function rejectUser(Request $request, User $user)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => __('messages.unauthorize')], 403);
        }

        $user->update(['is_approved' => false]);

        return response()->json([
            'status'  => 'success',
            'message' => __('messages.reject'),
            'data'    => new UserResource($user)
        ]);
    }

    // ---------------------------
    // DELETE USER
    // ---------------------------
    public function deleteUser(Request $request, User $user)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => __('messages.unauthorize')], 403);
        }

        $user->delete();

        return response()->json([
            'status'  => 'success',
            'message' => __('messages.delete')
        ]);
    }
}