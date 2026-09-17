<?php

namespace App\Http\Controllers;

use App\Models\Auto;
use App\Models\Campaign;
use App\Models\Expense;
use App\Models\Vendor;

class ExpenseController extends ResourceController
{
    protected string $modelClass = Expense::class;
    protected string $routeBase = 'expenses';
    protected string $title = 'Expenses';
    protected string $singular = 'Expense';
    protected ?string $codePrefix = 'EXP';
    protected ?string $codeTable = 'expenses';
    protected array $searchable = ['code', 'category', 'notes'];
    protected array $with = ['vendor', 'auto', 'campaign'];
    protected string $statusColumn = 'payment_status';
    protected array $statuses = ['unpaid', 'paid'];

    protected function fields(): array
    {
        return [
            ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true],
            ['name' => 'category', 'label' => 'Category', 'type' => 'select', 'required' => true],
            ['name' => 'vendor_id', 'label' => 'Vendor', 'type' => 'select'],
            ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true],
            ['name' => 'tax_amount', 'label' => 'Tax Amount', 'type' => 'number'],
            ['name' => 'payment_status', 'label' => 'Payment Status', 'type' => 'select', 'required' => true],
            ['name' => 'auto_id', 'label' => 'Related Auto', 'type' => 'select'],
            ['name' => 'campaign_id', 'label' => 'Related Campaign', 'type' => 'select'],
            ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
        ];
    }

    protected function options(): array
    {
        return [
            'category' => array_combine(
                ['driver_payment', 'sim', 'hardware', 'installation', 'maintenance', 'cloud', 'storage', 'cdn', 'sms', 'whatsapp', 'api', 'vendor', 'other'],
                ['Driver Payment', 'SIM / Connectivity', 'Hardware', 'Installation', 'Maintenance', 'Cloud', 'Storage', 'CDN', 'SMS', 'WhatsApp', 'API', 'Vendor', 'Other']
            ),
            'vendor_id' => Vendor::orderBy('name')->pluck('name', 'id')->toArray(),
            'auto_id' => Auto::orderBy('registration_number')->pluck('registration_number', 'id')->toArray(),
            'campaign_id' => Campaign::orderBy('name')->pluck('name', 'id')->toArray(),
            'payment_status' => ['unpaid' => 'Unpaid', 'paid' => 'Paid'],
        ];
    }

    protected function transform(array $data, $request): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }

    protected function columns(): array
    {
        return [
            ['key' => 'code', 'label' => 'Code'],
            ['key' => 'date', 'label' => 'Date', 'render' => fn ($r) => $r->date?->format('d M Y')],
            ['key' => 'category', 'label' => 'Category', 'render' => fn ($r) => ucwords(str_replace('_', ' ', $r->category))],
            ['key' => 'amount', 'label' => 'Amount', 'render' => fn ($r) => \App\Support\Fmt::money($r->amount), 'strong' => true],
            ['key' => 'payment_status', 'label' => 'Status', 'badge' => true],
        ];
    }

    protected function indexExtra(): array
    {
        return [
            'cards' => [
                'This Month' => \App\Support\Fmt::money(Expense::whereMonth('date', now()->month)->sum('amount')),
                'Unpaid' => \App\Support\Fmt::money(Expense::where('payment_status', 'unpaid')->sum('amount')),
                'Total (YTD)' => \App\Support\Fmt::money(Expense::whereYear('date', now()->year)->sum('amount')),
            ],
        ];
    }
}
