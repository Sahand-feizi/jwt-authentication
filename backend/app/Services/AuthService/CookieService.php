<?php

namespace App\Services\AuthService;

class CookieService {
    protected function cookieOptions(): array
    {
        $secure = app()->environment('production');
        $sameSite = $secure ? 'none' : 'lax';

        return [
            '/',
            null,
            $secure,
            true,
            false,
            $sameSite,
        ];
    }

    public function accessToken(string $token){
        return cookie(
            'access_token',
            $token,
            60,
            ...$this->cookieOptions()
        );
    }

    public function refreshToken(string $token){
        return cookie(
            'refresh_token',
            $token,
            14 * 24 * 60,
            ...$this->cookieOptions()
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