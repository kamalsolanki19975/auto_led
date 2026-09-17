<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ResourceApiController extends Controller
{
    protected array $map = [
        'autos' => [\App\Models\Auto::class, ['owner', 'primaryDriver', 'city']],
        'owners' => [\App\Models\Owner::class, []],
        'drivers' => [\App\Models\Driver::class, ['owner']],
        'screens' => [\App\Models\Screen::class, ['auto']],
        'devices' => [\App\Models\Device::class, ['auto', 'sim']],
        'sims' => [\App\Models\Sim::class, ['auto']],
        'advertisers' => [\App\Models\Advertiser::class, []],
        'advertisements' => [\App\Models\Advertisement::class, ['advertiser']],
        'campaigns' => [\App\Models\Campaign::class, ['advertiser']],
        'playlists' => [\App\Models\Playlist::class, ['items']],
        'invoices' => [\App\Models\Invoice::class, ['advertiser']],
        'payments' => [\App\Models\Payment::class, []],
        'expenses' => [\App\Models\Expense::class, []],
        'settlements' => [\App\Models\DriverSettlement::class, ['driver']],
        'proof-of-play' => [\App\Models\ProofOfPlay::class, ['campaign', 'auto']],
        'runtime' => [\App\Models\RuntimeLog::class, ['auto', 'driver']],
    ];

    protected array $permissions = [
        'autos' => 'network.auto.view', 'owners' => 'network.owner.view', 'drivers' => 'network.driver.view',
        'screens' => 'network.screen.view', 'devices' => 'network.device.view', 'sims' => 'network.sim.view',
        'advertisers' => 'advertising.advertiser.view', 'advertisements' => 'advertising.advertisement.view',
        'campaigns' => 'advertising.campaign.view', 'playlists' => 'advertising.playlist.view',
        'invoices' => 'finance.invoice.view', 'payments' => 'finance.payment.view',
        'expenses' => 'finance.expense.view', 'settlements' => 'finance.settlement.view',
        'proof-of-play' => 'advertising.pop.view', 'runtime' => 'reports.view',
    ];

    // Portal-linked accounts may only ever read their OWN records on these resources.
    protected array $scopes = [
        'advertiser' => [
            'campaigns' => 'advertiser_id',
            'advertisements' => 'advertiser_id',
            'invoices' => 'advertiser_id',
            'payments' => 'advertiser_id',
            'proof-of-play' => 'advertiser_id',
        ],
        'driver' => [
            'settlements' => 'driver_id',
            'runtime' => 'driver_id',
        ],
        'owner' => [
            'autos' => 'owner_id',
            'drivers' => 'owner_id',
            'settlements' => 'owner_id',
        ],
    ];

    public function index(Request $request, string $resource)
    {
        [$model, $with] = $this->resolve($resource);
        $query = $this->authorize($request, $model::query()->with($with), $resource);
        $rows = $query->paginate(min((int) $request->get('per_page', 25), 100));
        return response()->json(['success' => true, 'data' => $rows->items(), 'meta' => [
            'current_page' => $rows->currentPage(), 'last_page' => $rows->lastPage(), 'total' => $rows->total(),
        ]]);
    }

    public function show(Request $request, string $resource, $id)
    {
        [$model, $with] = $this->resolve($resource);
        $row = $this->authorize($request, $model::query()->with($with), $resource)->findOrFail($id);
        return response()->json(['success' => true, 'data' => $row]);
    }

    /** Enforce token ability + ownership scoping; returns the (possibly scoped) query. */
    protected function authorize(Request $request, Builder $query, string $resource): Builder
    {
        $user = $request->user();
        if ($user->isSuperAdmin()) {
            return $query;
        }

        $perm = $this->permissions[$resource];
        abort_unless($user->tokenCan($perm) || $user->tokenCan('*'), 403, 'Token lacks the required ability.');

        $role = $user->advertiser_id ? 'advertiser' : ($user->driver_id ? 'driver' : ($user->owner_id ? 'owner' : null));
        if ($role) {
            $column = $this->scopes[$role][$resource] ?? null;
            abort_unless($column, 403, 'This resource is not available to your account type.');
            $foreignKey = $user->{$role.'_id'};
            if ($resource === 'proof-of-play' && $role === 'advertiser') {
                return $query->whereHas('campaign', fn ($c) => $c->where('advertiser_id', $foreignKey));
            }
            return $query->where($column, $foreignKey);
        }
        return $query;
    }

    protected function resolve(string $resource): array
    {
        abort_unless(isset($this->map[$resource]), 404, 'Unknown resource.');
        return $this->map[$resource];
    }
}
