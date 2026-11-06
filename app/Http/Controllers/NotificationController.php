<?php

namespace App\Http\Controllers;

use App\Services\Notification\AppNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        protected AppNotificationService $notificationService
    ) {}

    public function index(Request $request): JsonResponse|View
    {
        $user = Auth::user();
        $notifications = $this->notificationService->getRecentForUser($user, 20);
        $unreadCount = $this->notificationService->getUnreadCount($user);

        if ($request->wantsJson()) {
            return response()->json([
                'unread_count' => $unreadCount,
                'notifications' => $notifications,
            ]);
        }

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markAsRead(int $id): JsonResponse
    {
        $user = Auth::user();
        $success = $this->notificationService->markAsRead($id, $user);

        return response()->json([
            'success' => $success,
            'unread_count' => $this->notificationService->getUnreadCount($user),
        ]);
    }

    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        $count = $this->notificationService->markAllAsRead($user);

        return response()->json([
            'success' => true,
            'updated_count' => $count,
            'unread_count' => 0,
        ]);
    }
}

