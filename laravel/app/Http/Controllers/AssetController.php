<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Vendor;

class AssetController extends ResourceController
{
    protected string $modelClass = Asset::class;
    protected string $routeBase = 'assets';
    protected string $title = 'Assets & Inventory';
    protected string $singular = 'Asset';
    protected ?string $codePrefix = 'AST';
    protected ?string $codeTable = 'assets';
    protected array $searchable = ['name', 'code', 'serial_number'];
    protected array $with = ['vendor'];
    protected array $statuses = ['in_stock', 'assigned', 'in_repair', 'scrapped'];

    protected function fields(): array
    {
        return [
            ['name' => 'asset_type', 'label' => 'Asset Type', 'type' => 'select', 'required' => true],
            ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
            ['name' => 'serial_number', 'label' => 'Serial Number', 'type' => 'text'],
            ['name' => 'vendor_id', 'label' => 'Vendor', 'type' => 'select'],
            ['name' => 'purchase_date', 'label' => 'Purchase Date', 'type' => 'date'],
            ['name' => 'cost', 'label' => 'Cost', 'type' => 'number'],
            ['name' => 'warranty_end', 'label' => 'Warranty End', 'type' => 'date'],
            ['name' => 'location', 'label' => 'Location', 'type' => 'text'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true],
        ];
    }

    protected function options(): array
    {
        return [
            'asset_type' => ['screen' => 'Screen', 'device' => 'Android Device', 'power_controller' => 'Power Controller', 'mounting_bracket' => 'Mounting Bracket', 'cable' => 'Cable', 'adapter' => 'Adapter', 'accessory' => 'Accessory'],
            'vendor_id' => Vendor::orderBy('name')->pluck('name', 'id')->toArray(),
            'status' => array_combine($this->statuses, array_map(fn ($s) => ucwords(str_replace('_', ' ', $s)), $this->statuses)),
        ];
    }

    protected function columns(): array
    {
        return [
            ['key' => 'code', 'label' => 'Code'],
            ['key' => 'name', 'label' => 'Asset', 'strong' => true],
            ['key' => 'asset_type', 'label' => 'Type', 'render' => fn ($r) => ucwords(str_replace('_', ' ', $r->asset_type))],
            ['key' => 'cost', 'label' => 'Cost', 'render' => fn ($r) => \App\Support\Fmt::money($r->cost)],
            ['key' => 'status', 'label' => 'Status', 'badge' => true],
        ];
    }
}
