<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()->notifications,
        ]);
    }

    public function unread(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()->unreadNotifications,
        ]);
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.notify_read'),
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.notify_delete'),
        ]);
    }
}
