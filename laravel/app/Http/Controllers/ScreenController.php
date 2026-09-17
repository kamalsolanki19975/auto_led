<?php

namespace App\Http\Controllers;

use App\Models\Screen;

class ScreenController extends ResourceController
{
    protected string $modelClass = Screen::class;
    protected string $routeBase = 'screens';
    protected string $title = 'Screens';
    protected string $singular = 'Screen';
    protected ?string $codePrefix = 'SCR';
    protected ?string $codeTable = 'screens';
    protected array $searchable = ['serial_number', 'code', 'brand', 'model'];
    protected array $with = ['auto'];
    protected array $statuses = ['inventory', 'installation_pending', 'installed', 'active', 'faulty', 'under_repair', 'replaced', 'decommissioned'];

    protected function fields(): array
    {
        return [
            ['name' => 'screen_type', 'label' => 'Screen Type', 'type' => 'select', 'required' => true],
            ['name' => 'brand', 'label' => 'Brand', 'type' => 'text'],
            ['name' => 'model', 'label' => 'Model', 'type' => 'text'],
            ['name' => 'serial_number', 'label' => 'Serial Number', 'type' => 'text', 'required' => true],
            ['name' => 'size', 'label' => 'Size', 'type' => 'text'],
            ['name' => 'resolution', 'label' => 'Resolution', 'type' => 'text'],
            ['name' => 'orientation', 'label' => 'Orientation', 'type' => 'select'],
            ['name' => 'installation_date', 'label' => 'Installation Date', 'type' => 'date'],
            ['name' => 'warranty_end', 'label' => 'Warranty End', 'type' => 'date'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true],
        ];
    }

    protected function options(): array
    {
        return [
            'screen_type' => ['lcd' => 'LCD', 'led' => 'LED', 'oled' => 'OLED'],
            'orientation' => ['landscape' => 'Landscape', 'portrait' => 'Portrait'],
            'status' => array_combine($this->statuses, array_map(fn ($s) => ucwords(str_replace('_', ' ', $s)), $this->statuses)),
        ];
    }

    protected function columns(): array
    {
        return [
            ['key' => 'code', 'label' => 'Code'],
            ['key' => 'serial_number', 'label' => 'Serial', 'strong' => true],
            ['key' => 'brand', 'label' => 'Brand'],
            ['key' => 'size', 'label' => 'Size'],
            ['key' => 'auto', 'label' => 'Auto', 'render' => fn ($r) => $r->auto?->registration_number ?? '—'],
            ['key' => 'status', 'label' => 'Status', 'badge' => true],
        ];
    }
}
