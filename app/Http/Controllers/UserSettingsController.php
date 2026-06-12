<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserSettingsController extends Controller
{
    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);
        $request->user()->update([
            'fcm_token' => $request->fcm_token,
        ]);
        return response()->json([
            'status' => 'success',
            'message' =>__('message.fcm_updated'),
        ]);
    }
    public function updateLanguage(Request $request)
    {
        $request->validate([
            'language' => 'required|in:ar,en',
        ]);
        $request->user()->update([
            'language' => $request->language,
        ]);
        return response()->json([
            'status' => 'success',
            'message' => __('message.language_updated'),
        ]);
    }
    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:light,dark',
        ]);
        $request->user()->update([
            'theme' => $request->theme,
        ]);
        return response()->json([
            'status' => 'success',
            'message' => __('message.theme_updated'),
        ]);
    }
}
