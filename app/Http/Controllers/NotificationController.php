<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $accountId = Auth::id();
        $limit = (int) $request->input('limit', 10);
        $limit = max(1, min($limit, 50));

        $notifications = Notification::with('sender')
            ->where('recipient_id', $accountId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function (Notification $notification) {
                return [
                    'id' => $notification->notification_id,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'is_read' => $notification->is_read,
                    'created_at' => optional($notification->created_at)->toDateTimeString(),
                    'time_for_humans' => optional($notification->created_at)->diffForHumans(),
                    'sender' => $notification->sender?->username,
                    'table_name' => $notification->table_name,
                    'record_id' => $notification->record_id,
                ];
            });

        $unreadCount = Notification::where('recipient_id', $accountId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'status' => 'success',
            'unread_count' => $unreadCount,
            'data' => $notifications,
        ]);
    }

    public function markAllRead()
    {
        $accountId = Auth::id();

        Notification::where('recipient_id', $accountId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read.',
            'unread_count' => 0,
        ]);
    }

    public function markAsRead(Notification $notification)
    {
        $accountId = Auth::id();

        abort_unless($notification->recipient_id === $accountId, 403);

        $notification->markAsRead();

        $unreadCount = Notification::where('recipient_id', $accountId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read.',
            'unread_count' => $unreadCount,
        ]);
    }
}
