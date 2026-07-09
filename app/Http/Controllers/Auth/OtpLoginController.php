<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OtpLoginController extends Controller
{
    public function create(): View
    {
        return view('auth.otp-verification');
    }

    public function send(Request $request, OtpService $otp): RedirectResponse
    {
        $validated = $request->validate(['email' => ['required', 'email']]);
        $user = User::query()->where('email', $validated['email'])->first();

        if ($user) {
            $code = $otp->send($user);
            $request->session()->put('otp_login_user_id', $user->id);
            $request->session()->flash('status', app()->isLocal() ? "OTP sent. Local code: {$code}" : 'OTP sent.');
        }

        return redirect()->route('otp.verification')->with('otp_email', $validated['email']);
    }

    public function verify(Request $request, OtpService $otp): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['nullable', 'email'],
            'otp' => ['required', 'array', 'size:6'],
            'otp.*' => ['required', 'digits:1'],
        ]);

        $code = implode('', $validated['otp']);
        $user = User::query()->find($request->session()->get('otp_login_user_id'));

        if (! $user && filled($validated['email'] ?? null)) {
            $user = User::query()->where('email', $validated['email'])->first();
        }

        if (! $user || ! $otp->verify($user, $code)) {
            throw ValidationException::withMessages(['otp' => 'The verification code is invalid or expired.']);
        }

        Auth::login($user, remember: true);
        $request->session()->forget('otp_login_user_id');
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
