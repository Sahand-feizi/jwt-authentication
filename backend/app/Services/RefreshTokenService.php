<?php

namespace App\Services;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

class RefreshTokenService
{
    public static function create(User $user)
    {
        $refreshToken = Str::random(64);
        
        RefreshToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $refreshToken),
            'expires_at' => Date::now()->addWeeks(2)
        ]);

        return $refreshToken;
    }
}
