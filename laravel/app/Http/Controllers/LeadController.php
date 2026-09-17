<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    protected array $statuses = ['new', 'contacted', 'qualified', 'proposal', 'converted', 'lost'];

    public function index(Request $request)
    {
        $query = Lead::query()->with('assignee')->latest();

        if ($s = $request->get('status')) {
            $query->where('status', $s);
        }
        if ($t = $request->get('type')) {
            $query->where('type', $t);
        }
        if ($q = $request->get('q')) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%")
                    ->orWhere('company', 'like', "%$q%")
                    ->orWhere('code', 'like', "%$q%");
            });
        }

        $counts = Lead::selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c', 'status')->toArray();

        return view('admin.leads.index', [
            'rows' => $query->paginate(20)->withQueryString(),
            'counts' => $counts,
            'total' => Lead::count(),
            'statuses' => $this->statuses,
            'agents' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Lead $lead)
    {
        return view('admin.leads.show', [
            'lead' => $lead->load('assignee'),
            'statuses' => $this->statuses,
            'agents' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'status' => 'required|in:'.implode(',', $this->statuses),
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'followup_at' => 'nullable|date',
        ]);

        $lead->update($data);
        AuditService::log('lead.updated', $lead, ['status' => $lead->status]);

        return back()->with('success', 'Lead '.$lead->code.' updated.');
    }
}
