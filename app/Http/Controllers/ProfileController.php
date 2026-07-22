<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UserSettingsUpdateRequest;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view($request->user()->hasRole('admin') ? 'profile.admin-edit' : 'profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function settings(Request $request): View
    {
        return view('profile.settings', ['user' => $request->user()]);
    }

    public function updateSettings(UserSettingsUpdateRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return Redirect::route('settings')->with('status', 'settings-updated');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request, MediaService $media): RedirectResponse
    {
        $validated = $request->validated();
        $storedPhoto = null;

        try {
            DB::transaction(function () use ($request, $media, &$validated, &$storedPhoto): void {
                if ($request->hasFile('profile_photo')) {
                    $storedPhoto = $media->store($request->file('profile_photo'), 'profile-photos', $request->user(), $request->user());
                    $path = $storedPhoto->metadata['path'] ?? null;
                    $validated['profile_photo_path'] = $path ? '/storage/'.ltrim($path, '/') : null;
                }

                unset($validated['profile_photo']);

                $request->user()->fill($validated);

                if ($request->user()->isDirty('email')) {
                    $request->user()->email_verified_at = null;
                }

                $request->user()->save();
            });
        } catch (Throwable $exception) {
            if ($storedPhoto instanceof Media) {
                $media->discard($storedPhoto);
            }

            throw $exception;
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->forceDelete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
