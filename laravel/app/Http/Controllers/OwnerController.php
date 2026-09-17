<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Owner;

class OwnerController extends ResourceController
{
    protected string $modelClass = Owner::class;
    protected string $routeBase = 'owners';
    protected string $title = 'Owners';
    protected string $singular = 'Owner';
    protected ?string $codePrefix = 'OWN';
    protected ?string $codeTable = 'owners';
    protected array $searchable = ['name', 'code', 'mobile', 'email'];
    protected array $with = ['city'];
    protected array $statuses = ['active', 'inactive'];

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
            ['name' => 'mobile', 'label' => 'Mobile', 'type' => 'text'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
            ['name' => 'address', 'label' => 'Address', 'type' => 'textarea'],
            ['name' => 'city_id', 'label' => 'City', 'type' => 'select'],
            ['name' => 'pan', 'label' => 'PAN', 'type' => 'text'],
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
            ['key' => 'autos', 'label' => 'Autos', 'render' => fn ($r) => $r->autos()->count()],
            ['key' => 'status', 'label' => 'Status', 'badge' => true],
        ];
    }

    protected function showExtra($model): array
    {
        return [
            'autos' => $model->autos()->with('primaryDriver')->get(),
            'settlements' => $model->settlements()->latest()->get(),
        ];
    }

    protected function showView(): string
    {
        return 'network.owner-show';
    }
}
