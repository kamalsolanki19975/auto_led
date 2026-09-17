<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $q = Notification::where('user_id', auth()->id());
        $filter = $request->get('filter', 'all');
        if ($filter === 'unread') {
            $q->whereNull('read_at');
        } elseif (in_array($filter, ['critical', 'warning', 'information', 'success'])) {
            $q->where('type', $filter);
        }
        return view('admin.notifications', [
            'rows' => $q->latest()->paginate(20)->withQueryString(),
            'filter' => $filter,
        ]);
    }

    public function dropdown()
    {
        return response()->json([
            'unread' => Notification::where('user_id', auth()->id())->whereNull('read_at')->count(),
            'items' => Notification::where('user_id', auth()->id())->latest()->limit(10)->get()->map(fn ($n) => [
                'id' => $n->id, 'type' => $n->type, 'title' => $n->title, 'message' => $n->message,
                'time' => $n->created_at->diffForHumans(), 'read' => (bool) $n->read_at, 'link' => $n->link,
            ]),
        ]);
    }

    public function read(Notification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        $notification->update(['read_at' => now()]);
        if (request()->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return $notification->link ? redirect($notification->link) : back();
    }

    public function readAll()
    {
        Notification::where('user_id', auth()->id())->whereNull('read_at')->update(['read_at' => now()]);
        if (request()->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('success', 'All notifications marked as read.');
    }
}
