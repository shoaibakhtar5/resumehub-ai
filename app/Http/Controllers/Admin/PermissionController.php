<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PermissionRequest;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Permission::class);
        $permissions = Permission::query()->withCount('roles');

        if ($search = trim($request->string('search')->toString())) {
            $permissions->where(fn (Builder $query) => $query
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%'));
        }

        return view('admin.permissions.index', [
            'permissions' => $permissions->orderBy('name')->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Permission::class);

        return view('admin.permissions.form', ['permission' => null]);
    }

    public function store(PermissionRequest $request): RedirectResponse
    {
        $permission = Permission::query()->create($request->validated() + ['guard_name' => 'web']);

        return redirect()->route('admin.permissions.show', $permission)->with('status', 'Permission created successfully.');
    }

    public function show(Permission $permission): View
    {
        $this->authorize('view', $permission);
        $permission->load('roles')->loadCount('roles');

        return view('admin.permissions.show', compact('permission'));
    }

    public function edit(Permission $permission): View
    {
        $this->authorize('update', $permission);

        return view('admin.permissions.form', compact('permission'));
    }

    public function update(PermissionRequest $request, Permission $permission): RedirectResponse
    {
        $attributes = $request->validated();
        if ($permission->is_system) {
            unset($attributes['name']);
        }
        $permission->update($attributes);

        return redirect()->route('admin.permissions.show', $permission)->with('status', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $this->authorize('delete', $permission);
        $permission->delete();

        return redirect()->route('admin.permissions')->with('status', 'Permission deleted successfully.');
    }
}
