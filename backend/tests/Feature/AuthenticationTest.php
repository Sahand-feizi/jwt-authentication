<?php

use App\Models\RefreshToken;
use App\Models\User;
use App\Services\AuthService\CookieService;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('should get an otp', function () {
    $response = $this->postJson('/api/auth/getOtp', [
        'phoneNumber' => '09900000000',
    ]);

    $response->assertOk();
});

it('should check the otp and log the user in', function () {
    $phoneNumber = '09900000000';
    $code = '111111';

    $user = User::factory()->create([
        'phoneNumber' => $phoneNumber,
        'code' => [
            'value' => $code,
            'expires_at' => Date::now()->addMinutes(2)
        ]
    ]);

    $this->postJson('/api/auth/checkOtp', [
        'phoneNumber' => $phoneNumber,
        'code' => $code
    ])
        ->assertStatus(200)
        ->assertCookie('refresh_token')
        ->assertCookie('access_token');
});

it('should return an error for an invalid otp', function () {
    $phoneNumber = '09900000000';
    $code = '111111';

    $user = User::factory()->create([
        'phoneNumber' => $phoneNumber,
        'code' => [
            'value' => $code,
            'expires_at' => Date::now()->addMinutes(2)
        ]
    ]);

    $this->postJson('/api/auth/checkOtp', [
        'phoneNumber' => $phoneNumber,
        'code' => '000000'
    ])
        ->assertStatus(401)
        ->assertJson([
            'message' => 'The code is wrong or has been expired',
        ])
        ->assertCookieMissing('refresh_token')
        ->assertCookieMissing('access_token');
});

it('should return an error for an expired otp', function () {
    $phoneNumber = '09900000000';
    $code = '111111';

    $user = User::factory()->create([
        'phoneNumber' => $phoneNumber,
        'code' => [
            'value' => $code,
            'expires_at' => Date::now()->subMinutes(1)
        ]
    ]);

    $this->postJson('/api/auth/checkOtp', [
        'phoneNumber' => $phoneNumber,
        'code' => $code
    ])
        ->assertStatus(401)
        ->assertJson([
            'message' => 'The code is wrong or has been expired',
        ])
        ->assertCookieMissing('refresh_token')
        ->assertCookieMissing('access_token');
});

it('should complete the user profile', function () {
    $user = User::factory()->create([
        'active' => false,
    ]);

    $this->actingAs($user);

    $response = $this->postJson('/api/auth/completeProfile', [
        'name' => 'John Doe',
        'email' => 'john.doe@example.com'
    ]);

    $response->assertStatus(200);

    $user->refresh();

    expect($user->toArray())->toMatchArray([
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'active' => true
    ]);
});

it('should return an error for an unauthenticated user trying to complete the profile', function () {
    $response = $this->postJson('/api/auth/completeProfile', [
        'name' => 'John Doe',
        'email' => 'john.doe@example.com'
    ]);

    $response->assertStatus(401);
});

it('should refresh the access token', function () {
    $user = User::factory()->create();
    $refreshToken = Str::random(64);

    RefreshToken::create([
        'user_id' => $user->id,
        'token' => hash('sha256', $refreshToken),
        'expires_at' => Date::now()->addWeeks(2),
    ]);

    $response =  $this->call('POST', '/api/auth/refresh', [], ['refresh_token' => $refreshToken]);

    $response->assertStatus(200)
        ->assertCookie('access_token')
        ->assertCookie('refresh_token');
});

it('should return an error for an invalid refresh token', function () {
    $response =  $this->call('POST', '/api/auth/refresh', [], ['refresh_token' => 'invalid_token']);

    $response->assertStatus(401)
        ->assertCookieMissing('access_token')
        ->assertCookieMissing('refresh_token');
});

it('should return an error for an expired refresh token', function () {
    $user = User::factory()->create();
    $refreshToken = Str::random(64);

    RefreshToken::create([
        'user_id' => $user->id,
        'token' => hash('sha256', $refreshToken),
        'expires_at' => Date::now()->subMinutes(1),
    ]);

    $response =  $this->call(
        'POST',
        '/api/auth/refresh',
        [],
        ['refresh_token' => $refreshToken]
    );

    $response->assertStatus(401)
        ->assertCookieMissing('access_token')
        ->assertCookieMissing('refresh_token');
});

it('should logout the user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $accessToken = Auth::login($user);

    $refreshToken = Str::random(64);

    RefreshToken::create([
        'user_id' => $user->id,
        'token' => hash('sha256', $refreshToken),
        'expires_at' => Date::now()->subMinutes(1),
    ]);

    $response = $this->call(
        'POST',
        '/api/auth/logout',
        [],
        ['refresh_token' => $refreshToken, 'access_token' => $accessToken]
    );

    $response->assertStatus(200);

    $this->assertGuest();
});
