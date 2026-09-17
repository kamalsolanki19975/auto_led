<?php

namespace App\Http\Controllers;

use App\Models\Warranty;

class WarrantyController extends ResourceController
{
    protected string $modelClass = Warranty::class;
    protected string $routeBase = 'warranties';
    protected string $title = 'Warranties';
    protected string $singular = 'Warranty';
    protected array $searchable = ['provider', 'asset_type'];

    protected function fields(): array
    {
        return [
            ['name' => 'asset_type', 'label' => 'Asset Type', 'type' => 'select', 'required' => true],
            ['name' => 'asset_id', 'label' => 'Asset ID', 'type' => 'number'],
            ['name' => 'provider', 'label' => 'Warranty Provider', 'type' => 'text', 'required' => true],
            ['name' => 'start_date', 'label' => 'Start Date', 'type' => 'date'],
            ['name' => 'end_date', 'label' => 'End Date', 'type' => 'date'],
            ['name' => 'terms', 'label' => 'Terms', 'type' => 'textarea'],
            ['name' => 'claim_status', 'label' => 'Claim Status', 'type' => 'select'],
        ];
    }

    protected function options(): array
    {
        return [
            'asset_type' => ['screen' => 'Screen', 'device' => 'Device', 'power_controller' => 'Power Controller'],
            'claim_status' => ['none' => 'None', 'raised' => 'Raised', 'approved' => 'Approved', 'rejected' => 'Rejected'],
        ];
    }

    protected function columns(): array
    {
        return [
            ['key' => 'asset_type', 'label' => 'Asset', 'render' => fn ($r) => ucwords($r->asset_type)],
            ['key' => 'provider', 'label' => 'Provider', 'strong' => true],
            ['key' => 'end_date', 'label' => 'Expires', 'render' => fn ($r) => $r->end_date?->format('d M Y') ?? '—'],
            ['key' => 'claim_status', 'label' => 'Claim', 'badge' => true],
        ];
    }

    protected function details($model): array
    {
        return parent::details($model);
    }

    protected function showView(): string
    {
        return 'resources.show';
    }

    protected function statusColumnDefault(): string
    {
        return 'claim_status';
    }
}
