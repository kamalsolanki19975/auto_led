<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use App\Support\Codes;
use Illuminate\Http\Request;

abstract class ResourceController extends Controller
{
    protected string $modelClass;
    protected string $routeBase;
    protected string $title;
    protected string $singular;
    protected ?string $codePrefix = null;
    protected ?string $codeTable = null;
    protected array $searchable = [];
    protected array $with = [];
    protected array $statuses = [];   // for status filter dropdown
    protected string $statusColumn = 'status';

    abstract protected function fields(): array;
    abstract protected function columns(): array;

    /** Select options keyed by field name. */
    protected function options(): array
    {
        return [];
    }

    /** Extra config for the index view (e.g. row actions). */
    protected function indexExtra(): array
    {
        return [];
    }

    protected function query()
    {
        return $this->modelClass::query()->with($this->with);
    }

    public function index(Request $request)
    {
        $q = $this->query();
        if ($search = $request->get('q')) {
            $q->where(function ($sub) use ($search) {
                foreach ($this->searchable as $col) {
                    $sub->orWhere($col, 'like', '%'.$search.'%');
                }
            });
        }
        if (($status = $request->get('status')) && $status !== 'all') {
            $q->where($this->statusColumn, $status);
        }
        $rows = $q->latest()->paginate(15)->withQueryString();

        return view('resources.index', [
            'title' => $this->title,
            'singular' => $this->singular,
            'routeBase' => $this->routeBase,
            'columns' => $this->columns(),
            'rows' => $rows,
            'statuses' => $this->statuses,
            'statusColumn' => $this->statusColumn,
            'canCreate' => !empty($this->fields()),
            'extra' => $this->indexExtra(),
        ]);
    }

    public function create()
    {
        return view('resources.form', $this->formData(new $this->modelClass));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data = $this->transform($data, $request);
        if ($this->codePrefix && $this->codeTable) {
            $data['code'] = Codes::next($this->codeTable, $this->codePrefix);
        }
        $model = $this->modelClass::create($data);
        $this->afterSave($model, $request, true);
        AuditService::log($this->routeBase.'.created', $model, [], $data);

        return redirect()->route($this->routeBase.'.show', $model)
            ->with('success', $this->singular.' created successfully.');
    }

    public function show($id)
    {
        $model = $this->query()->findOrFail($id);
        return view($this->showView(), array_merge([
            'title' => $this->title,
            'singular' => $this->singular,
            'routeBase' => $this->routeBase,
            'model' => $model,
            'details' => $this->details($model),
            'fields' => $this->fields(),
        ], $this->showExtra($model)));
    }

    public function edit($id)
    {
        $model = $this->modelClass::findOrFail($id);
        return view('resources.form', $this->formData($model));
    }

    public function update(Request $request, $id)
    {
        $model = $this->modelClass::findOrFail($id);
        $old = $model->getAttributes();
        $data = $request->validate($this->rules());
        $data = $this->transform($data, $request);
        $model->update($data);
        $this->afterSave($model, $request, false);
        AuditService::log($this->routeBase.'.updated', $model, $old, $data);

        return redirect()->route($this->routeBase.'.show', $model)
            ->with('success', $this->singular.' updated successfully.');
    }

    public function destroy($id)
    {
        $model = $this->modelClass::findOrFail($id);
        $model->delete();
        AuditService::log($this->routeBase.'.deleted', $model);
        return redirect()->route($this->routeBase.'.index')
            ->with('success', $this->singular.' removed.');
    }

    /* ---------- helpers ---------- */

    protected function rules(): array
    {
        $rules = [];
        foreach ($this->fields() as $f) {
            $r = [];
            $r[] = ($f['required'] ?? false) ? 'required' : 'nullable';
            $r[] = match ($f['type'] ?? 'text') {
                'number' => 'numeric',
                'email' => 'email',
                'date' => 'date',
                default => 'string',
            };
            $rules[$f['name']] = implode('|', $r);
        }
        return $rules;
    }

    protected function transform(array $data, Request $request): array
    {
        return $data;
    }

    protected function afterSave($model, Request $request, bool $creating): void {}

    protected function formData($model): array
    {
        return [
            'title' => $this->title,
            'singular' => $this->singular,
            'routeBase' => $this->routeBase,
            'model' => $model,
            'fields' => $this->fields(),
            'options' => $this->options(),
        ];
    }

    protected function details($model): array
    {
        $out = [];
        foreach ($this->fields() as $f) {
            $out[$f['label']] = $model->{$f['name']};
        }
        return $out;
    }

    protected function showView(): string
    {
        return 'resources.show';
    }

    protected function showExtra($model): array
    {
        return [];
    }
}
