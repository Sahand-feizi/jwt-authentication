<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Date;

class OtpService
{
    public function generateOtp(User $user): string
    {
        $code = (string) random_int(100000, 999999);

        $user->update([
            "code" => [
                "value" => $code,
                "expires_at" => Date::now()->addMinutes(2)
            ]
        ]);

        return $code;
    }

    public function verify(User $user): bool
    {
        return $user->otpExpired();
    }
}
