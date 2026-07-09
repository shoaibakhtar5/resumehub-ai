<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends Controller
{
    private const PROVIDERS = ['google', 'linkedin-openid'];

    public function redirect(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        if (! config("services.{$provider}.client_id")) {
            return redirect()->route('login')->with('status', Str::headline($provider).' login is not configured yet.');
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Throwable) {
            return redirect()->route('login')->withErrors(['email' => 'Social login could not be completed.']);
        }

        $email = $socialUser->getEmail();
        abort_unless($email, 422, 'The social provider did not return an email address.');

        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: Str::before($email, '@'),
                'password' => Hash::make(Str::random(40)),
                'profile_photo_path' => $socialUser->getAvatar(),
                'email_verified_at' => now(),
            ]
        );
        $user->assignRole('member');

        SocialAccount::query()->updateOrCreate(
            ['provider' => $provider, 'provider_user_id' => $socialUser->getId()],
            [
                'user_id' => $user->id,
                'provider_email' => $email,
                'provider_avatar_url' => $socialUser->getAvatar(),
                'access_token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
                'token_expires_at' => isset($socialUser->expiresIn) ? now()->addSeconds($socialUser->expiresIn) : null,
                'raw_profile' => $socialUser->user,
            ]
        );

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
