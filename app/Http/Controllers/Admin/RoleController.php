<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Role::class);
        $roles = Role::query()->withCount(['users', 'permissions']);

        if ($search = trim($request->string('search')->toString())) {
            $roles->where(fn (Builder $query) => $query
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%'));
        }

        return view('admin.roles.index', ['roles' => $roles->orderBy('name')->paginate(15)->withQueryString()]);
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);

        return view('admin.roles.form', ['role' => null, 'permissions' => $this->permissions()]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $role = DB::transaction(function () use ($request): Role {
            $role = Role::query()->create($request->safe()->only(['name', 'description']) + ['guard_name' => 'web']);
            $role->permissions()->sync($request->input('permission_ids', []));

            return $role;
        });

        return redirect()->route('admin.roles.show', $role)->with('status', 'Role created successfully.');
    }

    public function show(Role $role): View
    {
        $this->authorize('view', $role);
        $role->load(['permissions', 'users'])->loadCount(['users', 'permissions']);

        return view('admin.roles.show', compact('role'));
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);
        $role->load('permissions');

        return view('admin.roles.form', ['role' => $role, 'permissions' => $this->permissions()]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        DB::transaction(function () use ($request, $role): void {
            $attributes = $request->safe()->only(['name', 'description']);
            if ($role->is_system) {
                unset($attributes['name']);
            }
            $role->update($attributes);
            $permissionIds = $request->input('permission_ids', []);
            if ($role->is_system && $role->name === 'admin') {
                $adminAccessId = Permission::query()->where('name', 'admin.access')->value('id');
                if ($adminAccessId) {
                    $permissionIds[] = $adminAccessId;
                }
            }
            $role->permissions()->sync(array_values(array_unique($permissionIds)));
        });

        return redirect()->route('admin.roles.show', $role)->with('status', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);
        $role->delete();

        return redirect()->route('admin.roles')->with('status', 'Role deleted successfully.');
    }

    private function permissions()
    {
        return Permission::query()->orderBy('name')->get()->groupBy(fn (Permission $permission) => str($permission->name)->before('.')->headline()->toString());
    }
}
