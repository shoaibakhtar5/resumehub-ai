<?php

namespace App\Services\Auth;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public function send(User $user, string $purpose = 'login'): string
    {
        $code = (string) random_int(100000, 999999);

        OtpCode::query()->where('identifier', $user->email)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        OtpCode::query()->create([
            'user_id' => $user->id,
            'identifier' => $user->email,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        Mail::raw("Your ResumeHub AI verification code is {$code}. It expires in 10 minutes.", function ($message) use ($user): void {
            $message->to($user->email)->subject('Your ResumeHub AI verification code');
        });

        return $code;
    }

    public function verify(User $user, string $code, string $purpose = 'login'): bool
    {
        $otp = OtpCode::query()
            ->where('user_id', $user->id)
            ->where('identifier', $user->email)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->where('expires_at', '>=', now())
            ->latest()
            ->first();

        if (! $otp || ! Hash::check($code, $otp->code_hash)) {
            if ($otp) {
                $otp->increment('attempts');
            }

            return false;
        }

        $otp->forceFill(['consumed_at' => now()])->save();

        return true;
    }
}
