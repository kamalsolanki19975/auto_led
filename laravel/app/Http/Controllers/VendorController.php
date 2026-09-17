<?php

namespace App\Http\Controllers;

use App\Models\Vendor;

class VendorController extends ResourceController
{
    protected string $modelClass = Vendor::class;
    protected string $routeBase = 'vendors';
    protected string $title = 'Vendors';
    protected string $singular = 'Vendor';
    protected ?string $codePrefix = 'VEN';
    protected ?string $codeTable = 'vendors';
    protected array $searchable = ['name', 'code', 'email', 'gstin'];
    protected array $statuses = ['active', 'inactive'];

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Vendor Name', 'type' => 'text', 'required' => true],
            ['name' => 'contact_person', 'label' => 'Contact Person', 'type' => 'text'],
            ['name' => 'phone', 'label' => 'Phone', 'type' => 'text'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
            ['name' => 'address', 'label' => 'Address', 'type' => 'textarea'],
            ['name' => 'gstin', 'label' => 'GSTIN', 'type' => 'text'],
            ['name' => 'services', 'label' => 'Services', 'type' => 'text'],
            ['name' => 'payment_terms', 'label' => 'Payment Terms', 'type' => 'text'],
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
            ['key' => 'name', 'label' => 'Vendor', 'strong' => true],
            ['key' => 'contact_person', 'label' => 'Contact'],
            ['key' => 'phone', 'label' => 'Phone'],
            ['key' => 'status', 'label' => 'Status', 'badge' => true],
        ];
    }
}
