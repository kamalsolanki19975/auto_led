<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\Advertiser;
use App\Services\AdvertisementService;
use Illuminate\Http\Request;

class AdvertisementController extends ResourceController
{
    protected string $modelClass = Advertisement::class;
    protected string $routeBase = 'advertisements';
    protected string $title = 'Advertisements';
    protected string $singular = 'Advertisement';
    protected ?string $codePrefix = 'AD';
    protected ?string $codeTable = 'advertisements';
    protected array $searchable = ['title', 'code'];
    protected array $with = ['advertiser'];
    protected string $statusColumn = 'approval_status';
    protected array $statuses = ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'scheduled', 'active', 'completed', 'archived'];

    protected function fields(): array
    {
        return [
            ['name' => 'advertiser_id', 'label' => 'Advertiser', 'type' => 'select', 'required' => true],
            ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ['name' => 'content_type', 'label' => 'Content Type', 'type' => 'select', 'required' => true],
            ['name' => 'media_url', 'label' => 'Media URL', 'type' => 'text'],
            ['name' => 'text_content', 'label' => 'Text Content', 'type' => 'textarea'],
            ['name' => 'duration', 'label' => 'Duration (sec)', 'type' => 'number', 'required' => true],
            ['name' => 'resolution', 'label' => 'Resolution', 'type' => 'text'],
            ['name' => 'orientation', 'label' => 'Orientation', 'type' => 'select'],
            ['name' => 'category', 'label' => 'Category', 'type' => 'text'],
            ['name' => 'start_date', 'label' => 'Start Date', 'type' => 'date'],
            ['name' => 'end_date', 'label' => 'End Date', 'type' => 'date'],
        ];
    }

    protected function options(): array
    {
        return [
            'advertiser_id' => Advertiser::orderBy('company_name')->pluck('company_name', 'id')->toArray(),
            'content_type' => ['image' => 'Image', 'video' => 'Video', 'text' => 'Text', 'html' => 'HTML / Web'],
            'orientation' => ['landscape' => 'Landscape', 'portrait' => 'Portrait'],
        ];
    }

    protected function transform(array $data, Request $request): array
    {
        if ($request->routeIs($this->routeBase.'.store')) {
            $data['approval_status'] = 'draft';
            $data['created_by'] = auth()->id();
        }
        return $data;
    }

    protected function handleUpload(Advertisement $ad, Request $request): void
    {
        if ($request->hasFile('media')) {
            $path = $request->file('media')->store('advertisements', 'public');
            $ad->update(['media_path' => $path]);
        }
    }

    protected function afterSave($model, Request $request, bool $creating): void
    {
        $this->handleUpload($model, $request);
    }

    protected function columns(): array
    {
        return [
            ['key' => 'code', 'label' => 'Code'],
            ['key' => 'title', 'label' => 'Title', 'strong' => true],
            ['key' => 'advertiser', 'label' => 'Advertiser', 'render' => fn ($r) => $r->advertiser?->company_name ?? '—'],
            ['key' => 'content_type', 'label' => 'Type'],
            ['key' => 'duration', 'label' => 'Duration', 'render' => fn ($r) => $r->duration.'s'],
            ['key' => 'approval_status', 'label' => 'Approval', 'badge' => true],
        ];
    }

    protected function showView(): string
    {
        return 'advertising.advertisement-show';
    }

    protected function showExtra($model): array
    {
        return ['versions' => $model->versions()->latest()->get(), 'campaigns' => $model->campaigns()->get()];
    }

    // approval queue
    public function approvals()
    {
        $rows = Advertisement::with('advertiser')->whereIn('approval_status', ['submitted', 'under_review'])->latest()->paginate(15);
        return view('advertising.approvals', ['rows' => $rows]);
    }

    public function submit(Advertisement $advertisement, AdvertisementService $svc)
    {
        $svc->submit($advertisement);
        return back()->with('success', 'Advertisement submitted for review.');
    }

    public function approve(Advertisement $advertisement, AdvertisementService $svc)
    {
        $svc->approve($advertisement, auth()->id());
        return back()->with('success', 'Advertisement approved.');
    }

    public function reject(Request $request, Advertisement $advertisement, AdvertisementService $svc)
    {
        $data = $request->validate(['reason' => 'required|string']);
        $svc->reject($advertisement, $data['reason'], auth()->id());
        return back()->with('success', 'Advertisement rejected.');
    }
}
