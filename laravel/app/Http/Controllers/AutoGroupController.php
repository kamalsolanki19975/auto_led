<?php

namespace App\Http\Controllers;

use App\Models\AutoGroup;
use App\Models\City;

class AutoGroupController extends ResourceController
{
    protected string $modelClass = AutoGroup::class;
    protected string $routeBase = 'auto-groups';
    protected string $title = 'Auto Groups';
    protected string $singular = 'Auto Group';
    protected ?string $codePrefix = 'GRP';
    protected ?string $codeTable = 'auto_groups';
    protected array $searchable = ['name', 'code'];
    protected array $with = ['city'];

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
            ['name' => 'city_id', 'label' => 'City', 'type' => 'select'],
            ['name' => 'type', 'label' => 'Type', 'type' => 'text'],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
        ];
    }

    protected function options(): array
    {
        return ['city_id' => City::orderBy('name')->pluck('name', 'id')->toArray()];
    }

    protected function columns(): array
    {
        return [
            ['key' => 'code', 'label' => 'Code'],
            ['key' => 'name', 'label' => 'Name', 'strong' => true],
            ['key' => 'city', 'label' => 'City', 'render' => fn ($r) => $r->city?->name ?? '—'],
            ['key' => 'autos', 'label' => 'Autos', 'render' => fn ($r) => $r->autos()->count()],
        ];
    }

    protected function showExtra($model): array
    {
        return ['autos' => $model->autos()->with('primaryDriver')->get()];
    }
}
