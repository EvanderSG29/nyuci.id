<?php

namespace App\Support\Auth;

use Carbon\CarbonInterface;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Carbon;

class RegisterOtpState
{
    public const EMAIL_KEY = 'auth.register_otp.email';

    public const EXPIRES_AT_KEY = 'auth.register_otp.expires_at';

    public const LAST_SENT_AT_KEY = 'auth.register_otp.last_sent_at';

    public static function put(Session $session, string $email, CarbonInterface $expiresAt, CarbonInterface $lastSentAt): void
    {
        $session->put([
            self::EMAIL_KEY => $email,
            self::EXPIRES_AT_KEY => $expiresAt->toIso8601String(),
            self::LAST_SENT_AT_KEY => $lastSentAt->toIso8601String(),
        ]);
    }

    public static function email(Session $session): ?string
    {
        $email = $session->get(self::EMAIL_KEY);

        return is_string($email) && $email !== '' ? $email : null;
    }

    public static function expiresAt(Session $session): ?Carbon
    {
        return self::carbonFromSession($session, self::EXPIRES_AT_KEY);
    }

    public static function resendAvailableAt(Session $session, int $cooldownSeconds): ?Carbon
    {
        $lastSentAt = self::carbonFromSession($session, self::LAST_SENT_AT_KEY);

        return $lastSentAt?->copy()->addSeconds($cooldownSeconds);
    }

    public static function clear(Session $session): void
    {
        $session->forget([
            self::EMAIL_KEY,
            self::EXPIRES_AT_KEY,
            self::LAST_SENT_AT_KEY,
        ]);
    }

    protected static function carbonFromSession(Session $session, string $key): ?Carbon
    {
        $value = $session->get($key);

        if (! is_string($value) || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }
}
