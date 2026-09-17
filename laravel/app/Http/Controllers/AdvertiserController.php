<?php

namespace App\Http\Controllers;

use App\Models\Advertiser;

class AdvertiserController extends ResourceController
{
    protected string $modelClass = Advertiser::class;
    protected string $routeBase = 'advertisers';
    protected string $title = 'Advertisers';
    protected string $singular = 'Advertiser';
    protected ?string $codePrefix = 'ADV';
    protected ?string $codeTable = 'advertisers';
    protected array $searchable = ['company_name', 'code', 'email', 'gstin'];
    protected array $statuses = ['active', 'inactive'];

    protected function fields(): array
    {
        return [
            ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true],
            ['name' => 'contact_person', 'label' => 'Contact Person', 'type' => 'text'],
            ['name' => 'mobile', 'label' => 'Mobile', 'type' => 'text'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
            ['name' => 'address', 'label' => 'Address', 'type' => 'textarea'],
            ['name' => 'gstin', 'label' => 'GSTIN', 'type' => 'text'],
            ['name' => 'pan', 'label' => 'PAN', 'type' => 'text'],
            ['name' => 'billing_address', 'label' => 'Billing Address', 'type' => 'textarea'],
            ['name' => 'payment_terms', 'label' => 'Payment Terms', 'type' => 'text'],
            ['name' => 'credit_limit', 'label' => 'Credit Limit', 'type' => 'number'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true],
        ];
    }

    protected function options(): array
    {
        return ['status' => ['active' => 'Active', 'inactive' => 'Inactive']];
    }

    protected function columns(): array
    {
        return [
            ['key' => 'code', 'label' => 'Code'],
            ['key' => 'company_name', 'label' => 'Company', 'strong' => true],
            ['key' => 'contact_person', 'label' => 'Contact'],
            ['key' => 'campaigns', 'label' => 'Campaigns', 'render' => fn ($r) => $r->campaigns()->count()],
            ['key' => 'outstanding', 'label' => 'Outstanding', 'render' => fn ($r) => \App\Support\Fmt::money($r->outstanding())],
            ['key' => 'status', 'label' => 'Status', 'badge' => true],
        ];
    }

    protected function showView(): string
    {
        return 'advertising.advertiser-show';
    }

    protected function showExtra($model): array
    {
        return [
            'ads' => $model->advertisements()->latest()->get(),
            'campaigns' => $model->campaigns()->latest()->get(),
            'invoices' => $model->invoices()->latest()->get(),
            'payments' => $model->payments()->latest()->limit(10)->get(),
            'outstanding' => $model->outstanding(),
        ];
    }
}
