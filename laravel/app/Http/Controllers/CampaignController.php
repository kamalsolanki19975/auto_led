<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\Advertiser;
use App\Models\Auto;
use App\Models\AutoGroup;
use App\Models\Campaign;
use App\Services\AuditService;
use App\Services\CampaignService;
use App\Services\InvoiceService;
use App\Support\Codes;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function __construct(protected CampaignService $service) {}

    public function index(Request $request)
    {
        $q = Campaign::with('advertiser');
        if ($s = $request->get('q')) {
            $q->where(fn ($x) => $x->where('name', 'like', "%$s%")->orWhere('code', 'like', "%$s%"));
        }
        if (($status = $request->get('status')) && $status !== 'all') {
            $q->where('status', $status);
        }
        // advertiser portal scoping
        if (auth()->user()->primaryPortal() === 'advertiser') {
            $q->where('advertiser_id', auth()->user()->advertiser_id);
        }
        return view('advertising.campaign-index', [
            'rows' => $q->latest()->paginate(15)->withQueryString(),
            'service' => $this->service,
            'statuses' => ['draft', 'pending_approval', 'approved', 'scheduled', 'active', 'paused', 'under_delivery', 'completed', 'expired', 'cancelled'],
        ]);
    }

    public function create()
    {
        return view('advertising.campaign-form', [
            'model' => new Campaign(['priority' => 2, 'pricing_model' => 'per_play', 'campaign_type' => 'paid', 'target_type' => 'autos']),
            'advertisers' => Advertiser::orderBy('company_name')->pluck('company_name', 'id'),
            'ads' => Advertisement::where('approval_status', 'approved')->get(),
            'groups' => AutoGroup::pluck('name', 'id'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['code'] = Codes::next('campaigns', 'CMP');
        $data['status'] = 'draft';
        $data['created_by'] = auth()->id();
        $campaign = Campaign::create($data);
        if ($request->filled('advertisements')) {
            $campaign->advertisements()->sync($request->input('advertisements'));
        }
        if ($request->filled('autos')) {
            $this->service->assignAutos($campaign, $request->input('autos'));
        }
        AuditService::log('campaign.created', $campaign, [], $data);
        app(\App\Services\WebhookService::class)->dispatch('campaign.created', ['code' => $campaign->code]);
        return redirect()->route('campaigns.show', $campaign)->with('success', 'Campaign created.');
    }

    public function show(Campaign $campaign)
    {
        $delivery = $this->service->delivery($campaign);
        $profit = app(\App\Services\ProfitabilityService::class)->campaign($campaign);
        return view('advertising.campaign-show', [
            'campaign' => $campaign->load('advertiser', 'advertisements', 'assignments.auto', 'invoices'),
            'delivery' => $delivery,
            'profit' => $profit,
            'audit' => \App\Models\AuditLog::where('entity_type', 'Campaign')->where('entity_id', $campaign->id)->latest()->limit(20)->get(),
            'availableAutos' => Auto::whereNotIn('status', ['sold', 'decommissioned'])->limit(200)->get(['id', 'registration_number', 'code']),
            'approvedAds' => Advertisement::where('approval_status', 'approved')->get(['id', 'title', 'code']),
        ]);
    }

    public function edit(Campaign $campaign)
    {
        return view('advertising.campaign-form', [
            'model' => $campaign,
            'advertisers' => Advertiser::orderBy('company_name')->pluck('company_name', 'id'),
            'ads' => Advertisement::where('approval_status', 'approved')->get(),
            'groups' => AutoGroup::pluck('name', 'id'),
        ]);
    }

    public function update(Request $request, Campaign $campaign)
    {
        $data = $this->validateData($request);
        $campaign->update($data);
        if ($request->has('advertisements')) {
            $campaign->advertisements()->sync($request->input('advertisements', []));
        }
        AuditService::log('campaign.updated', $campaign);
        return redirect()->route('campaigns.show', $campaign)->with('success', 'Campaign updated.');
    }

    public function assign(Request $request, Campaign $campaign)
    {
        $data = $request->validate(['autos' => 'required|array']);
        $n = $this->service->assignAutos($campaign, $data['autos']);
        return back()->with('success', $n.' auto(s) assigned to campaign.');
    }

    public function approve(Campaign $campaign)
    {
        $this->service->approve($campaign, auth()->id());
        return back()->with('success', 'Campaign approved.');
    }

    public function activate(Campaign $campaign)
    {
        $this->service->activate($campaign, auth()->id());
        return back()->with('success', 'Campaign activated and is now live.');
    }

    public function pause(Campaign $campaign)
    {
        $this->service->pause($campaign);
        return back()->with('success', 'Campaign paused.');
    }

    public function invoice(Campaign $campaign, InvoiceService $svc)
    {
        $invoice = $svc->createForCampaign($campaign, [], auth()->id());
        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice '.$invoice->number.' generated.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'advertiser_id' => 'required|exists:advertisers,id',
            'name' => 'required|string',
            'campaign_type' => 'required|in:paid,house,emergency',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'budget' => 'nullable|numeric',
            'pricing_model' => 'required|string',
            'rate' => 'nullable|numeric',
            'target_type' => 'required|string',
            'priority' => 'nullable|integer',
            'expected_plays' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);
    }
}
