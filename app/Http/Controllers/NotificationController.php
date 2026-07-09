<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => Notification::where('user_id',$request->user()->id)->get(),
        ]);
    }

    public function unread(Request $request)
    {
        $unread=Notification::where('user_id',$request->user()->id)
    ->where('is_read',false)
    ->get();
        return response()->json([
            'status' => 'success',
            'data' => $unread,
        ]);
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = Notification::where('id',$id)
    ->where('user_id',$request->user()->id)
    ->firstOrFail();

$notification->update([
    'is_read'=>true
]);
        return response()->json([
            'status' => 'success',
            'message' => __('message.notify_read'),
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $notification= Notification::where('id',$id)
    ->where('user_id',$request->user()->id)
    ->firstOrFail();

$notification->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('message.notify_delete'),
        ]);
    }
}
