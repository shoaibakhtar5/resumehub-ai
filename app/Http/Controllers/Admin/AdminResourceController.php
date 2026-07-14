<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminResourceRequest;
use App\Services\Admin\AdminDashboardService;
use App\Services\Admin\AdminResourceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminResourceController extends Controller
{
    public function __construct(
        private readonly AdminDashboardService $dashboardService,
        private readonly AdminResourceService $resourceService,
    ) {}

    public function dashboard(Request $request): View
    {
        $dates = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        return view('admin.dashboard', $this->dashboardService->data(
            $request->user(),
            $dates['from'] ?? null,
            $dates['to'] ?? null,
        ));
    }

    public function index(Request $request, string $resource): View
    {
        $definition = $this->definition($resource);
        $query = $definition['model']::query()->with($definition['with']);

        if (in_array('Illuminate\\Database\\Eloquent\\SoftDeletes', class_uses_recursive($definition['model']), true)) {
            $query->withoutTrashed();
        }

        if ($search = trim($request->string('search')->toString())) {
            $query->where(function (Builder $builder) use ($definition, $search): void {
                foreach ($definition['searchable'] as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $builder->{$method}($column, 'like', '%'.$search.'%');
                }
            });
        }

        if ($status = $request->string('status')->toString()) {
            if (in_array('status', $definition['fillable'], true)) {
                $query->where('status', $status);
            } elseif (in_array('is_active', $definition['fillable'], true)) {
                $query->where('is_active', $status === 'active');
            }
        }

        $sortable = array_values(array_filter($definition['columns'], fn (string $column) => ! str_contains($column, '.')));
        $sort = in_array($request->string('sort')->toString(), $sortable, true) ? $request->string('sort')->toString() : 'id';
        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
        $perPage = in_array($request->integer('per_page'), [10, 15, 25, 50], true) ? $request->integer('per_page') : 10;

        return view('admin.resources.index', [
            'resource' => $resource,
            'definition' => $definition,
            'records' => $query->orderBy($sort, $direction)->paginate($perPage)->withQueryString(),
        ]);
    }

    public function create(string $resource): View
    {
        $definition = $this->definition($resource);
        abort_if($definition['readonly'], 404);

        return view('admin.resources.form', [
            'resource' => $resource,
            'definition' => $definition,
            'record' => null,
            'options' => $this->resourceService->options($resource),
        ]);
    }

    public function show(string $resource, string $id): View
    {
        $definition = $this->definition($resource);

        return view('admin.resources.show', [
            'resource' => $resource,
            'definition' => $definition,
            'record' => $this->record($definition, $id),
        ]);
    }

    public function edit(string $resource, string $id): View
    {
        $definition = $this->definition($resource);
        abort_if($definition['readonly'], 404);

        return view('admin.resources.form', [
            'resource' => $resource,
            'definition' => $definition,
            'record' => $this->record($definition, $id),
            'options' => $this->resourceService->options($resource),
        ]);
    }

    public function store(AdminResourceRequest $request, string $resource): RedirectResponse
    {
        $definition = $this->definition($resource);
        abort_if($definition['readonly'], 404);

        DB::transaction(function () use ($request, $resource, $definition): void {
            $record = $definition['model']::query()->create($this->resourceService->payload($request, $resource, $definition));
            $this->resourceService->syncRelations($record, $request, $resource);
        });

        return redirect()->route('admin.'.$resource)->with('status', $definition['title'].' record created.');
    }

    public function update(AdminResourceRequest $request, string $resource, string $id): RedirectResponse
    {
        $definition = $this->definition($resource);
        abort_if($definition['readonly'], 404);

        DB::transaction(function () use ($request, $resource, $definition, $id): void {
            $record = $this->record($definition, $id);
            $record->fill($this->resourceService->payload($request, $resource, $definition, $record))->save();
            $this->resourceService->syncRelations($record, $request, $resource);
        });

        return redirect()->route('admin.'.$resource)->with('status', $definition['title'].' record updated.');
    }

    public function destroy(Request $request, string $resource, string $id): RedirectResponse
    {
        $definition = $this->definition($resource);
        abort_if($definition['readonly'], 404);
        $record = $this->record($definition, $id);

        if ($resource === 'users' && (int) $record->getKey() === (int) $request->user()->getKey()) {
            return back()->withErrors(['delete' => 'You cannot delete the administrator account you are using.']);
        }

        if (in_array($resource, ['roles', 'permissions'], true) && $record->is_system) {
            return back()->withErrors(['delete' => 'System access records are protected.']);
        }

        $record->delete();

        return redirect()->route('admin.'.$resource)->with('status', $definition['title'].' record deleted.');
    }

    public function bulk(Request $request, string $resource): RedirectResponse
    {
        $definition = $this->definition($resource);
        abort_if($definition['readonly'], 404);
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['string'],
            'action' => ['required', 'in:delete,activate,deactivate'],
        ]);
        $query = $definition['model']::query()->whereIn((new $definition['model'])->getKeyName(), $validated['ids']);

        if ($resource === 'users') {
            $query->whereKeyNot($request->user()->getKey());
        }

        if (in_array($resource, ['roles', 'permissions'], true)) {
            $query->where('is_system', false);
        }

        match ($validated['action']) {
            'delete' => $query->delete(),
            'activate' => $this->setActive($query, $definition, true),
            'deactivate' => $this->setActive($query, $definition, false),
        };

        return back()->with('status', 'Bulk action completed.');
    }

    private function setActive(Builder $query, array $definition, bool $active): void
    {
        if (in_array('is_active', $definition['fillable'], true)) {
            $query->update(['is_active' => $active]);
        } elseif (in_array('status', $definition['fillable'], true)) {
            $query->update(['status' => $active ? 'active' : 'inactive']);
        }
    }

    private function definition(string $resource): array
    {
        abort_if(in_array($resource, ['users', 'roles', 'permissions', 'templates'], true), 404);

        $definition = $this->resourceService->definition($resource);
        abort_unless($definition, 404);

        return $definition;
    }

    private function record(array $definition, string $id): Model
    {
        return $definition['model']::query()->with($definition['with'])->findOrFail($id);
    }
}
