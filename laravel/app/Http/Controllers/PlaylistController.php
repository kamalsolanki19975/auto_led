<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Playlist;

class PlaylistController extends ResourceController
{
    protected string $modelClass = Playlist::class;
    protected string $routeBase = 'playlists';
    protected string $title = 'Playlists';
    protected string $singular = 'Playlist';
    protected ?string $codePrefix = 'PL';
    protected ?string $codeTable = 'playlists';
    protected array $searchable = ['name', 'code'];
    protected array $with = ['campaign'];

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
            ['name' => 'campaign_id', 'label' => 'Campaign', 'type' => 'select'],
            ['name' => 'priority', 'label' => 'Priority (1=Emergency..4=Default)', 'type' => 'number'],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true],
        ];
    }

    protected function options(): array
    {
        return [
            'campaign_id' => Campaign::orderBy('name')->pluck('name', 'id')->toArray(),
            'status' => ['active' => 'Active', 'inactive' => 'Inactive'],
        ];
    }

    protected function columns(): array
    {
        return [
            ['key' => 'code', 'label' => 'Code'],
            ['key' => 'name', 'label' => 'Name', 'strong' => true],
            ['key' => 'campaign', 'label' => 'Campaign', 'render' => fn ($r) => $r->campaign?->name ?? 'General'],
            ['key' => 'priority', 'label' => 'Priority'],
            ['key' => 'items', 'label' => 'Items', 'render' => fn ($r) => $r->items()->count()],
            ['key' => 'status', 'label' => 'Status', 'badge' => true],
        ];
    }

    protected function showExtra($model): array
    {
        return ['items' => $model->items()->with('advertisement')->get()];
    }
}
