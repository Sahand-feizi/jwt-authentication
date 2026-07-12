<?php

namespace App\Services\AuthService;

class CookieService {
    public function accessToken(string $token){
        return cookie(
            'access_token',
            $token,
            60,
            '/',
            null,
            true,
            true
        );
    }

    public function refreshToken(string $token){
        return cookie(
            'refresh_token',
            $token,
            14 * 24 * 60,
            '/',
            null,
            true,
            true
        );
    }

    public function forgetAccess()
    {
        return cookie()->forget('access_token');
    }

    public function forgetRefresh()
    {
        return cookie()->forget('refresh_token');
    }
}