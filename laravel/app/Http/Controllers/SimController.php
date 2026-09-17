<?php

namespace App\Http\Controllers;

use App\Models\Sim;

class SimController extends ResourceController
{
    protected string $modelClass = Sim::class;
    protected string $routeBase = 'sims';
    protected string $title = 'SIM Management';
    protected string $singular = 'SIM';
    protected ?string $codePrefix = 'SIM';
    protected ?string $codeTable = 'sims';
    protected array $searchable = ['iccid', 'mobile_number', 'code', 'imsi'];
    protected array $with = ['auto', 'device'];
    protected array $statuses = ['available', 'active', 'suspended', 'expired', 'blocked', 'replaced', 'deactivated'];

    protected function fields(): array
    {
        return [
            ['name' => 'mobile_number', 'label' => 'Mobile Number', 'type' => 'text'],
            ['name' => 'iccid', 'label' => 'ICCID', 'type' => 'text', 'required' => true],
            ['name' => 'imsi', 'label' => 'IMSI', 'type' => 'text'],
            ['name' => 'operator', 'label' => 'Operator', 'type' => 'select'],
            ['name' => 'plan', 'label' => 'Plan', 'type' => 'text'],
            ['name' => 'monthly_cost', 'label' => 'Monthly Cost', 'type' => 'number'],
            ['name' => 'data_limit_mb', 'label' => 'Data Limit (MB)', 'type' => 'number'],
            ['name' => 'data_used_mb', 'label' => 'Data Used (MB)', 'type' => 'number'],
            ['name' => 'activation_date', 'label' => 'Activation Date', 'type' => 'date'],
            ['name' => 'renewal_date', 'label' => 'Renewal Date', 'type' => 'date'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true],
        ];
    }

    protected function options(): array
    {
        return [
            'operator' => ['Jio' => 'Jio', 'Airtel' => 'Airtel', 'Vi' => 'Vi', 'BSNL' => 'BSNL'],
            'status' => array_combine($this->statuses, array_map(fn ($s) => ucwords($s), $this->statuses)),
        ];
    }

    protected function columns(): array
    {
        return [
            ['key' => 'code', 'label' => 'Code'],
            ['key' => 'mobile_number', 'label' => 'Number', 'strong' => true],
            ['key' => 'operator', 'label' => 'Operator'],
            ['key' => 'auto', 'label' => 'Auto', 'render' => fn ($r) => $r->auto?->registration_number ?? '—'],
            ['key' => 'renewal_date', 'label' => 'Renewal', 'render' => fn ($r) => $r->renewal_date?->format('d M Y') ?? '—'],
            ['key' => 'status', 'label' => 'Status', 'badge' => true],
        ];
    }

    // SIM dashboard cards computed on index
    protected function indexExtra(): array
    {
        return [
            'cards' => [
                'Total SIMs' => Sim::count(),
                'Active' => Sim::where('status', 'active')->count(),
                'Suspended' => Sim::where('status', 'suspended')->count(),
                'Expiring (15d)' => Sim::whereBetween('renewal_date', [now(), now()->addDays(15)])->count(),
                'Monthly Cost' => \App\Support\Fmt::money(Sim::where('status', 'active')->sum('monthly_cost')),
            ],
        ];
    }
}
