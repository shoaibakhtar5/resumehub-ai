<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\Media;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);
        $query = User::query()->with('roles')->withCount('resumes');

        if ($search = trim($request->string('search')->toString())) {
            $query->where(fn (Builder $builder) => $builder
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%')
                ->orWhere('phone', 'like', '%'.$search.'%'));
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($roleId = $request->integer('role_id')) {
            $query->whereHas('roles', fn (Builder $builder) => $builder->whereKey($roleId));
        }

        $sort = in_array($request->string('sort')->toString(), ['name', 'email', 'status', 'created_at'], true)
            ? $request->string('sort')->toString()
            : 'created_at';
        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';

        return view('admin.users.index', [
            'users' => $query->orderBy($sort, $direction)->paginate(15)->withQueryString(),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.form', ['managedUser' => null, 'roles' => $this->roles()]);
    }

    public function store(StoreUserRequest $request, MediaService $media): RedirectResponse
    {
        $storedPhoto = null;
        try {
            $user = DB::transaction(function () use ($request, $media, &$storedPhoto): User {
                $data = Arr::except($request->validated(), ['profile_photo', 'role_ids', 'password_confirmation']);
                $user = User::query()->create($data);
                $storedPhoto = $this->storePhoto($request, $user, $media);
                $this->syncRoles($user, $request->input('role_ids', []), $request->user());

                return $user;
            });
        } catch (Throwable $exception) {
            if ($storedPhoto instanceof Media) {
                $media->discard($storedPhoto);
            }
            throw $exception;
        }

        return redirect()->route('admin.users.show', $user)->with('status', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);
        $user->load('roles.permissions')->loadCount('resumes');

        return view('admin.users.show', ['managedUser' => $user]);
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);
        $user->load('roles');

        return view('admin.users.form', ['managedUser' => $user, 'roles' => $this->roles()]);
    }

    public function update(UpdateUserRequest $request, User $user, MediaService $media): RedirectResponse
    {
        $storedPhoto = null;
        try {
            DB::transaction(function () use ($request, $user, $media, &$storedPhoto): void {
                $data = Arr::except($request->validated(), ['profile_photo', 'role_ids', 'password_confirmation']);
                if (blank($data['password'] ?? null)) {
                    unset($data['password']);
                }
                if (isset($data['email']) && $data['email'] !== $user->email) {
                    $data['email_verified_at'] = null;
                }
                $user->update($data);
                $storedPhoto = $this->storePhoto($request, $user, $media);
                $this->syncRoles($user, $request->input('role_ids', []), $request->user());
            });
        } catch (Throwable $exception) {
            if ($storedPhoto instanceof Media) {
                $media->discard($storedPhoto);
            }
            throw $exception;
        }

        return redirect()->route('admin.users.show', $user)->with('status', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('delete', $user);
        $user->delete();

        return redirect()->route('admin.users')->with('status', 'User deleted successfully.');
    }

    private function roles()
    {
        return Role::query()->withCount('permissions')->orderBy('name')->get();
    }

    private function syncRoles(User $user, array $roleIds, User $admin): void
    {
        $assignments = collect($roleIds)->mapWithKeys(fn ($roleId) => [
            $roleId => ['assigned_by_user_id' => $admin->id],
        ])->all();

        $user->roles()->sync($assignments);
    }

    private function storePhoto(Request $request, User $user, MediaService $media): ?Media
    {
        if (! $request->hasFile('profile_photo')) {
            return null;
        }

        $stored = $media->store($request->file('profile_photo'), 'profile-photos', $request->user(), $user, [
            'alt_text' => $user->name.' profile image',
        ]);
        $path = $stored->metadata['path'] ?? null;
        $user->forceFill(['profile_photo_path' => $path ? '/storage/'.ltrim($path, '/') : null])->save();

        return $stored;
    }
}
