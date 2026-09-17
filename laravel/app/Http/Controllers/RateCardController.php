<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\RateCard;

class RateCardController extends ResourceController
{
    protected string $modelClass = RateCard::class;
    protected string $routeBase = 'rate-cards';
    protected string $title = 'Driver Rate Cards';
    protected string $singular = 'Rate Card';
    protected ?string $codePrefix = 'RC';
    protected ?string $codeTable = 'rate_cards';
    protected array $searchable = ['code', 'auto_type'];
    protected array $with = ['city'];
    protected array $statuses = ['active', 'inactive'];

    protected function fields(): array
    {
        return [
            ['name' => 'city_id', 'label' => 'City', 'type' => 'select'],
            ['name' => 'auto_type', 'label' => 'Auto Type', 'type' => 'text'],
            ['name' => 'campaign_type', 'label' => 'Campaign Type', 'type' => 'select'],
            ['name' => 'rate_basis', 'label' => 'Rate Basis', 'type' => 'select', 'required' => true],
            ['name' => 'rate', 'label' => 'Rate', 'type' => 'number', 'required' => true],
            ['name' => 'minimum_guarantee', 'label' => 'Minimum Guarantee', 'type' => 'number'],
            ['name' => 'bonus', 'label' => 'Bonus', 'type' => 'number'],
            ['name' => 'penalty', 'label' => 'Penalty', 'type' => 'number'],
            ['name' => 'valid_from', 'label' => 'Valid From', 'type' => 'date'],
            ['name' => 'valid_to', 'label' => 'Valid To', 'type' => 'date'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true],
        ];
    }

    protected function options(): array
    {
        return [
            'city_id' => City::orderBy('name')->pluck('name', 'id')->toArray(),
            'campaign_type' => ['paid' => 'Paid', 'house' => 'House', 'emergency' => 'Emergency'],
            'rate_basis' => ['per_play' => 'Per Valid Playback', 'per_hour' => 'Per Hour', 'per_day' => 'Per Day', 'per_campaign' => 'Per Campaign', 'hybrid' => 'Hybrid'],
            'status' => ['active' => 'Active', 'inactive' => 'Inactive'],
        ];
    }

    protected function columns(): array
    {
        return [
            ['key' => 'code', 'label' => 'Code', 'strong' => true],
            ['key' => 'city', 'label' => 'City', 'render' => fn ($r) => $r->city?->name ?? 'All'],
            ['key' => 'rate_basis', 'label' => 'Basis', 'render' => fn ($r) => ucwords(str_replace('_', ' ', $r->rate_basis))],
            ['key' => 'rate', 'label' => 'Rate', 'render' => fn ($r) => \App\Support\Fmt::money($r->rate)],
            ['key' => 'minimum_guarantee', 'label' => 'Min. Guarantee', 'render' => fn ($r) => \App\Support\Fmt::money($r->minimum_guarantee)],
            ['key' => 'status', 'label' => 'Status', 'badge' => true],
        ];
    }
}
