<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Lấy danh sách thông báo mới nhất cho Staff
    public function index()
    {
        $notifications = Notification::orderBy('created_at', 'desc')
            ->take(20) // Lấy 20 cái gần nhất thôi cho nhẹ
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    // Đánh dấu đã đọc
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}
