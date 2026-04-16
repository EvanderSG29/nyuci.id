<?php

namespace App\Otp;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use SadiqSalau\LaravelOtp\Contracts\OtpInterface as OtpContract;

class RegisterUserOtp implements OtpContract
{
    public function __construct(
        public string $name,
        public string $email,
        public string $passwordHash,
    ) {}

    public function process(): User
    {
        $user = User::unguarded(fn () => User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->passwordHash,
            'email_verified_at' => now(),
        ]));

        event(new Registered($user));

        Auth::login($user);

        return $user;
    }
}
