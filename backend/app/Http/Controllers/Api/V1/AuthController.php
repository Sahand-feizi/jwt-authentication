<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CompleteProfile;
use App\Http\Controllers\Api\ApiController as ApiApiController;
use App\Http\Requests\V1\CheckOtpRequest;
use App\Http\Requests\V1\CompleteProfileRequest;
use App\Http\Requests\V1\GetOtpRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use App\Services\AuthenticationService;
use App\Services\CookieService;

class AuthController extends ApiApiController
{
    public function __construct(protected OtpService $otpService, protected AuthenticationService $authService, protected CookieService $cookieService)
    {
        //
    }

    public function getOtp(GetOtpRequest $request)
    {
        $code = (string) random_int(100000, 999999);
        $phoneNumber = $request->input('phoneNumber');

        $user = User::where('phoneNumber', $phoneNumber)->first() ??
            User::create([
                "phoneNumber" => $phoneNumber,
                "code" => [
                    "value" => $code,
                    "expires_at" => Date::now()->addMinutes(2)
                ]
            ]);

        $this->otpService->generateOtp($user);

        return $this->ok("code has sended to $phoneNumber successfuly");
    }

    public function checkOtp(CheckOtpRequest $request)
    {
        $user = User::where('phoneNumber', $request->phoneNumber)->firstOrFail();

        if (!$this->otpService->verify($user)) {
            if ($user->otpMatches($request->code)) {
                [$accessToken, $refreshToken] = $this->authService->login($user);

                return $this->withCookies(
                    "Wellcome to our site",
                    [
                        $this->cookieService->accessToken($accessToken),
                        $this->cookieService->refreshToken($refreshToken)
                    ]
                );
            }
        }

        return $this->ok(
            "The code is wrong or has been expired",
            ['status' => 401]
        );
    }

    public function CompleteProfile(CompleteProfileRequest $request, CompleteProfile $action)
    {
        $user = Auth::user();

        $action->handel($user, array_merge($request->validated(), ['active' => true]));

        return $this->ok("Profile completed successfully");
    }

    public function logout()
    {
        $this->authService->logout();

        return $this->withCookies(
            "logout successfuly",
            [
                $this->cookieService->forgetAccess(),
                $this->cookieService->forgetRefresh()
            ]
        );
    }
}
