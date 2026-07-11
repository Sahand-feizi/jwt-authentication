<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Date;
use Override;
use Tymon\JWTAuth\Contracts\JWTSubject;

#[Fillable(['name', 'email', 'phoneNumber', 'active', 'code'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'code' => 'array'
        ];
    }

    #[Override]
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    #[Override]
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function otpExpired()
    {
        return Date::parse($this->code['expires_at'])->isPast();
    }

    public function otpMatches(string $code) {
        return $this->code['value'] === $code;
    }
}
