<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\EmailLog;
use App\Models\ApiLog;
use App\Models\NotificationLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function audit(Request $request)
    {
        $q = AuditLog::with('user');
        if ($s = $request->get('q')) {
            $q->where('action', 'like', "%$s%")->orWhere('entity_type', 'like', "%$s%");
        }
        return view('admin.audit', ['rows' => $q->latest()->paginate(25)->withQueryString()]);
    }

    public function email(Request $request)
    {
        $q = EmailLog::query();
        if (($status = $request->get('status')) && $status !== 'all') {
            $q->where('status', $status);
        }
        return view('admin.email-logs', ['rows' => $q->latest()->paginate(25)->withQueryString(), 'statuses' => ['queued', 'sent', 'failed', 'retrying']]);
    }

    public function notifications(Request $request)
    {
        return view('admin.notification-logs', ['rows' => NotificationLog::with('user')->latest()->paginate(25)]);
    }

    public function api(Request $request)
    {
        return view('integrations.api-logs', ['rows' => ApiLog::latest()->paginate(30)]);
    }
}
