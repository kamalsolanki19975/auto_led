<?php

namespace App\Http\Controllers;

use App\Models\Auto;
use App\Models\Device;
use App\Models\Installation;
use App\Models\Screen;
use App\Models\Sim;
use App\Models\User;
use Illuminate\Http\Request;

class InstallationController extends ResourceController
{
    protected string $modelClass = Installation::class;
    protected string $routeBase = 'installations';
    protected string $title = 'Installations';
    protected string $singular = 'Installation';
    protected ?string $codePrefix = 'INS';
    protected ?string $codeTable = 'installations';
    protected array $searchable = ['code'];
    protected array $with = ['auto', 'technician'];
    protected array $statuses = ['scheduled', 'in_progress', 'testing', 'approval', 'completed', 'activated', 'cancelled'];

    protected function fields(): array
    {
        return [
            ['name' => 'auto_id', 'label' => 'Auto', 'type' => 'select', 'required' => true],
            ['name' => 'screen_id', 'label' => 'Screen', 'type' => 'select'],
            ['name' => 'device_id', 'label' => 'Device', 'type' => 'select'],
            ['name' => 'sim_id', 'label' => 'SIM', 'type' => 'select'],
            ['name' => 'technician_id', 'label' => 'Technician', 'type' => 'select'],
            ['name' => 'scheduled_at', 'label' => 'Scheduled At', 'type' => 'date'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true],
            ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
        ];
    }

    protected function options(): array
    {
        return [
            'auto_id' => Auto::orderBy('registration_number')->pluck('registration_number', 'id')->toArray(),
            'screen_id' => Screen::pluck('serial_number', 'id')->toArray(),
            'device_id' => Device::pluck('code', 'id')->toArray(),
            'sim_id' => Sim::pluck('mobile_number', 'id')->toArray(),
            'technician_id' => User::whereHas('roles', fn ($q) => $q->where('slug', 'technician'))->pluck('name', 'id')->toArray(),
            'status' => array_combine($this->statuses, array_map(fn ($s) => ucwords(str_replace('_', ' ', $s)), $this->statuses)),
        ];
    }

    protected function columns(): array
    {
        return [
            ['key' => 'code', 'label' => 'Code', 'strong' => true],
            ['key' => 'auto', 'label' => 'Auto', 'render' => fn ($r) => $r->auto?->registration_number ?? '—'],
            ['key' => 'technician', 'label' => 'Technician', 'render' => fn ($r) => $r->technician?->name ?? 'Unassigned'],
            ['key' => 'scheduled_at', 'label' => 'Scheduled', 'render' => fn ($r) => $r->scheduled_at?->format('d M Y') ?? '—'],
            ['key' => 'status', 'label' => 'Status', 'badge' => true],
        ];
    }
}
