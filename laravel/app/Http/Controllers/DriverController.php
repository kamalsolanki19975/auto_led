<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Driver;
use App\Models\Owner;

class DriverController extends ResourceController
{
    protected string $modelClass = Driver::class;
    protected string $routeBase = 'drivers';
    protected string $title = 'Drivers';
    protected string $singular = 'Driver';
    protected ?string $codePrefix = 'DRV';
    protected ?string $codeTable = 'drivers';
    protected array $searchable = ['name', 'code', 'mobile', 'license_no'];
    protected array $with = ['owner', 'city'];
    protected array $statuses = ['active', 'inactive'];

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
            ['name' => 'mobile', 'label' => 'Mobile', 'type' => 'text'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
            ['name' => 'address', 'label' => 'Address', 'type' => 'textarea'],
            ['name' => 'license_no', 'label' => 'Driving License', 'type' => 'text'],
            ['name' => 'license_expiry', 'label' => 'License Expiry', 'type' => 'date'],
            ['name' => 'owner_id', 'label' => 'Owner', 'type' => 'select'],
            ['name' => 'city_id', 'label' => 'City', 'type' => 'select'],
            ['name' => 'bank_name', 'label' => 'Bank Name', 'type' => 'text'],
            ['name' => 'bank_account', 'label' => 'Bank Account', 'type' => 'text'],
            ['name' => 'ifsc', 'label' => 'IFSC', 'type' => 'text'],
            ['name' => 'upi', 'label' => 'UPI', 'type' => 'text'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true],
        ];
    }

    protected function options(): array
    {
        return [
            'owner_id' => Owner::orderBy('name')->pluck('name', 'id')->toArray(),
            'city_id' => City::orderBy('name')->pluck('name', 'id')->toArray(),
            'status' => ['active' => 'Active', 'inactive' => 'Inactive'],
        ];
    }

    protected function columns(): array
    {
        return [
            ['key' => 'code', 'label' => 'Code'],
            ['key' => 'name', 'label' => 'Name', 'strong' => true],
            ['key' => 'mobile', 'label' => 'Mobile'],
            ['key' => 'owner', 'label' => 'Owner', 'render' => fn ($r) => $r->owner?->name ?? '—'],
            ['key' => 'status', 'label' => 'Status', 'badge' => true],
        ];
    }

    protected function showView(): string
    {
        return 'network.driver-show';
    }

    protected function showExtra($model): array
    {
        $runtime = \App\Models\RuntimeLog::where('driver_id', $model->id);
        return [
            'validRuntime' => (int) (clone $runtime)->sum('valid_advertising_runtime_seconds'),
            'validPlays' => (int) (clone $runtime)->sum('valid_plays'),
            'earnings' => $model->earnings()->latest()->get(),
            'settlements' => $model->settlements()->latest()->get(),
            'payments' => $model->payments()->latest()->get(),
            'disputes' => $model->disputes()->latest()->get(),
            'auto' => $model->autos()->first(),
        ];
    }
}
