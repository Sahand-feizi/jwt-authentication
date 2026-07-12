<?php

namespace App\Services\AuthService;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\AuthService\RefreshTokenService;
use App\Traits\ApiResponses;

class AuthenticationService
{
    use ApiResponses;

    public function __construct(protected RefreshTokenService $refreshTokenService) {}
    public function logout()
    {
        $user = Auth::user();

        RefreshToken::where('user_id', $user->id)->delete();

        Auth::logout();
    }

    public function login(User $user)
    {
        $accessToken = Auth::login($user);
        $refreshToken = $this->refreshTokenService->create($user);

        return [
            $accessToken,
            $refreshToken
        ];
    }

    public function refresh(string $refreshToken)
    {
        if (!$refreshToken) {
            return $this->error('you are not authenticated', 401);
        }

        $hashedToken = hash('sha256', $refreshToken);
        $dbToken = RefreshToken::where('token', $hashedToken)
            ->where('expires_at', '>', now())
            ->first();

        if (!$dbToken) {
            return $this->error('Your Token has been expired or It is not valid', 401);
        }

        $user = User::findOrFail($dbToken->user_id);
        $refreshToken = $this->refreshTokenService->update($dbToken);
        $newAccessToken = Auth::login($user);

        return [
            'refreshToken' => $refreshToken,
            'accessToken' => $newAccessToken
        ];
    }
}
