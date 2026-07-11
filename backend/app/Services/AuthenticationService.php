<?php

namespace App\Services;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\RefreshTokenService;

class AuthenticationService
{
    public function logout()
    {
        $user = Auth::user();

        RefreshToken::where('user_id', $user->id)->delete();

        Auth::logout();
    }

    public function login(User $user)
    {
        $accessToken = Auth::login($user);
        $refreshToken = RefreshTokenService::create($user);

        return [
            $accessToken,
            $refreshToken
        ];
    }
}
