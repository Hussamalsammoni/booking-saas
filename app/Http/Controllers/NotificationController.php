<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'items' => $user->notifications()
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn ($n) => [
                    'id'      => $n->id,
                    'title'   => $n->data['title'] ?? '',
                    'message' => $n->data['message'] ?? '',
                    'url'     => $n->data['url'] ?? null,
                    'read'    => $n->read_at !== null,
                    'time'    => $n->created_at->locale('ar')->diffForHumans(),
                ]),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['ok' => true]);
    }
}