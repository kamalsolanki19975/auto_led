<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

    public function index(Request $request, string $resource)
    {
        [$model, $with] = $this->resolve($resource);
        $rows = $model::with($with)->paginate(min((int) $request->get('per_page', 25), 100));
        return response()->json(['success' => true, 'data' => $rows->items(), 'meta' => [
            'current_page' => $rows->currentPage(), 'last_page' => $rows->lastPage(), 'total' => $rows->total(),
        ]]);
    }

    public function show(string $resource, $id)
    {
        [$model, $with] = $this->resolve($resource);
        $row = $model::with($with)->findOrFail($id);
        return response()->json(['success' => true, 'data' => $row]);
    }

    protected function resolve(string $resource): array
    {
        abort_unless(isset($this->map[$resource]), 404, 'Unknown resource.');
        return $this->map[$resource];
    }
}
