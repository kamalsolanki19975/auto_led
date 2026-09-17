<?php

namespace App\Http\Controllers;

use App\Models\Auto;
use App\Models\MaintenanceTicket;
use App\Models\User;
use App\Models\Vendor;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class MaintenanceController extends ResourceController
{
    protected string $modelClass = MaintenanceTicket::class;
    protected string $routeBase = 'maintenance';
    protected string $title = 'Maintenance & Service Tickets';
    protected string $singular = 'Ticket';
    protected ?string $codePrefix = 'TKT';
    protected ?string $codeTable = 'maintenance_tickets';
    protected array $searchable = ['code', 'issue'];
    protected array $with = ['auto', 'technician', 'vendor'];
    protected array $statuses = ['open', 'assigned', 'in_progress', 'waiting', 'resolved', 'closed'];

    protected function fields(): array
    {
        return [
            ['name' => 'auto_id', 'label' => 'Auto', 'type' => 'select'],
            ['name' => 'issue', 'label' => 'Issue', 'type' => 'text', 'required' => true],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ['name' => 'priority', 'label' => 'Priority', 'type' => 'select', 'required' => true],
            ['name' => 'technician_id', 'label' => 'Technician', 'type' => 'select'],
            ['name' => 'vendor_id', 'label' => 'Vendor', 'type' => 'select'],
            ['name' => 'cost', 'label' => 'Cost', 'type' => 'number'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true],
            ['name' => 'resolution', 'label' => 'Resolution', 'type' => 'textarea'],
        ];
    }

    protected function options(): array
    {
        return [
            'auto_id' => Auto::orderBy('registration_number')->pluck('registration_number', 'id')->toArray(),
            'priority' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'],
            'technician_id' => User::whereHas('roles', fn ($q) => $q->where('slug', 'technician'))->pluck('name', 'id')->toArray(),
            'vendor_id' => Vendor::orderBy('name')->pluck('name', 'id')->toArray(),
            'status' => array_combine($this->statuses, array_map(fn ($s) => ucwords(str_replace('_', ' ', $s)), $this->statuses)),
        ];
    }

    protected function transform(array $data, Request $request): array
    {
        if ($request->routeIs($this->routeBase.'.store')) {
            $data['created_by'] = auth()->id();
        }
        return $data;
    }

    protected function afterSave($model, Request $request, bool $creating): void
    {
        if ($creating) {
            app(NotificationService::class)->notifyAdmins('maintenance.created', [
                'type' => 'warning', 'category' => 'operations',
                'title' => 'Maintenance ticket created',
                'message' => $model->code.': '.$model->issue,
                'related_type' => 'MaintenanceTicket', 'related_id' => $model->id,
            ]);
        }
    }

    protected function columns(): array
    {
        return [
            ['key' => 'code', 'label' => 'Ticket', 'strong' => true],
            ['key' => 'auto', 'label' => 'Auto', 'render' => fn ($r) => $r->auto?->registration_number ?? '—'],
            ['key' => 'issue', 'label' => 'Issue'],
            ['key' => 'priority', 'label' => 'Priority', 'badge' => true],
            ['key' => 'status', 'label' => 'Status', 'badge' => true],
        ];
    }
}
