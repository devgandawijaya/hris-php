<?php

namespace App\Services\Notification;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AppNotificationService
{
    /**
     * Send an in-app notification to a specific user
     */
    public function sendToUser(
        User $user,
        string $title,
        string $message,
        string $type = 'info',
        ?string $actionUrl = null
    ): AppNotification {
        $tenantId = $user->tenants()->first()?->id ?? session('current_tenant_id');

        return AppNotification::create([
            'tenant_id' => $tenantId,
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type, // info, warning, success, urgent
            'action_url' => $actionUrl,
            'is_read' => false,
        ]);
    }

    /**
     * Broadcast an in-app notification to all users in a tenant
     */
    public function sendToTenant(
        string|int $tenantId,
        string $title,
        string $message,
        string $type = 'info',
        ?string $actionUrl = null
    ): AppNotification {
        return AppNotification::create([
            'tenant_id' => $tenantId,
            'user_id' => null, // null indicates tenant-wide broadcast
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'action_url' => $actionUrl,
            'is_read' => false,
        ]);
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead(int $id, User $user): bool
    {
        $notification = AppNotification::where('id', $id)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereNull('user_id');
            })
            ->first();

        if ($notification && !$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
            return true;
        }

        return false;
    }

    /**
     * Mark all unread notifications as read for a user
     */
    public function markAllAsRead(User $user): int
    {
        return AppNotification::where('is_read', false)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereNull('user_id');
            })
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Get recent notifications for a user (both direct and tenant broadcasts)
     */
    public function getRecentForUser(User $user, int $limit = 10): Collection
    {
        return AppNotification::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereNull('user_id');
            })
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get unread notification count for a user
     */
    public function getUnreadCount(User $user): int
    {
        return AppNotification::where('is_read', false)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereNull('user_id');
            })
            ->count();
    }
}

