<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Auto;
use App\Models\AutoGroup;
use App\Models\City;
use App\Models\Driver;
use App\Models\Owner;
use App\Services\ProfitabilityService;

class AutoController extends ResourceController
{
    protected string $modelClass = Auto::class;
    protected string $routeBase = 'autos';
    protected string $title = 'Autos';
    protected string $singular = 'Auto';
    protected ?string $codePrefix = 'AUTO';
    protected ?string $codeTable = 'autos';
    protected array $searchable = ['registration_number', 'code', 'chassis_number'];
    protected array $with = ['owner', 'primaryDriver', 'city'];
    protected array $statuses = ['available', 'active', 'inactive', 'installation_pending', 'screen_installed', 'under_maintenance', 'suspended', 'sold', 'decommissioned'];

    protected function fields(): array
    {
        return [
            ['name' => 'registration_number', 'label' => 'Registration Number', 'type' => 'text', 'required' => true],
            ['name' => 'vehicle_type', 'label' => 'Vehicle Type', 'type' => 'select', 'required' => true],
            ['name' => 'manufacturer', 'label' => 'Manufacturer', 'type' => 'text'],
            ['name' => 'model', 'label' => 'Model', 'type' => 'text'],
            ['name' => 'manufacturing_year', 'label' => 'Manufacturing Year', 'type' => 'number'],
            ['name' => 'chassis_number', 'label' => 'Chassis Number', 'type' => 'text'],
            ['name' => 'engine_number', 'label' => 'Engine Number', 'type' => 'text'],
            ['name' => 'owner_id', 'label' => 'Owner', 'type' => 'select'],
            ['name' => 'primary_driver_id', 'label' => 'Primary Driver', 'type' => 'select'],
            ['name' => 'secondary_driver_id', 'label' => 'Secondary Driver', 'type' => 'select'],
            ['name' => 'city_id', 'label' => 'City', 'type' => 'select'],
            ['name' => 'area_id', 'label' => 'Area', 'type' => 'select'],
            ['name' => 'auto_group_id', 'label' => 'Auto Group', 'type' => 'select'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true],
            ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea'],
        ];
    }

    protected function options(): array
    {
        return [
            'vehicle_type' => ['auto_rickshaw' => 'Auto Rickshaw', 'e_rickshaw' => 'E-Rickshaw'],
            'owner_id' => Owner::orderBy('name')->pluck('name', 'id')->toArray(),
            'primary_driver_id' => Driver::orderBy('name')->pluck('name', 'id')->toArray(),
            'secondary_driver_id' => Driver::orderBy('name')->pluck('name', 'id')->toArray(),
            'city_id' => City::orderBy('name')->pluck('name', 'id')->toArray(),
            'area_id' => Area::orderBy('name')->pluck('name', 'id')->toArray(),
            'auto_group_id' => AutoGroup::orderBy('name')->pluck('name', 'id')->toArray(),
            'status' => array_combine($this->statuses, array_map(fn ($s) => ucwords(str_replace('_', ' ', $s)), $this->statuses)),
        ];
    }

    protected function columns(): array
    {
        return [
            ['key' => 'code', 'label' => 'Code'],
            ['key' => 'registration_number', 'label' => 'Registration', 'strong' => true],
            ['key' => 'owner', 'label' => 'Owner', 'render' => fn ($r) => $r->owner?->name ?? '—'],
            ['key' => 'primaryDriver', 'label' => 'Driver', 'render' => fn ($r) => $r->primaryDriver?->name ?? '—'],
            ['key' => 'city', 'label' => 'City', 'render' => fn ($r) => $r->city?->name ?? '—'],
            ['key' => 'status', 'label' => 'Status', 'badge' => true],
        ];
    }

    protected function showView(): string
    {
        return 'network.auto-show';
    }

    protected function showExtra($model): array
    {
        $profit = app(ProfitabilityService::class)->auto($model);
        $runtime = \App\Models\RuntimeLog::where('auto_id', $model->id);
        return [
            'profit' => $profit,
            'runtimeSeconds' => (int) (clone $runtime)->sum('valid_advertising_runtime_seconds'),
            'validPlays' => (int) (clone $runtime)->sum('valid_plays'),
            'campaigns' => $model->campaigns()->with('advertiser')->get(),
            'maintenance' => $model->maintenanceTickets()->latest()->get(),
            'expenses' => $model->expenses()->latest()->limit(10)->get(),
            'assignments' => $model->device?->assignments()->latest()->get() ?? collect(),
            'audit' => \App\Models\AuditLog::where('entity_type', 'Auto')->where('entity_id', $model->id)->latest()->limit(15)->get(),
        ];
    }
}
