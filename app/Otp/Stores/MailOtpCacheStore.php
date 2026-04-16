<?php

namespace App\Otp\Stores;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use SadiqSalau\LaravelOtp\Contracts\OtpStoreInterface;

class MailOtpCacheStore implements OtpStoreInterface
{
    protected string $key;

    protected string $identifier;

    public function __construct(
        protected CacheRepository $cache,
        Request $request,
    ) {
        $this->key = (string) config('otp.store_key', 'otp');
        $this->identifier = md5((string) $request->ip());
    }

    public function identifier($identifier): void
    {
        $this->identifier = md5((string) $identifier);
    }

    public function put($otp): void
    {
        $this->cache->put(
            $this->getCacheKey(),
            [
                'otp_class' => $otp['otp']::class,
                // Store the OTP object as a string so database cache can persist it safely.
                'otp_payload' => base64_encode(serialize($otp['otp'])),
                'notifiable_mail' => $this->resolveMailAddress($otp['notifiable']),
                'code' => (string) $otp['code'],
                'expires' => $otp['expires']->toIso8601String(),
            ],
            $otp['expires'],
        );
    }

    public function retrieve(): ?array
    {
        $payload = $this->cache->get($this->getCacheKey());

        if (! is_array($payload)) {
            return null;
        }

        $serializedOtp = base64_decode((string) ($payload['otp_payload'] ?? ''), true);

        if ($serializedOtp === false) {
            return null;
        }

        $allowedClass = (string) ($payload['otp_class'] ?? '');
        $otp = unserialize($serializedOtp, ['allowed_classes' => [$allowedClass]]);

        if (! is_object($otp)) {
            return null;
        }

        return [
            'otp' => $otp,
            'notifiable' => Notification::route('mail', (string) $payload['notifiable_mail']),
            'code' => (string) $payload['code'],
            'expires' => Carbon::parse((string) $payload['expires']),
        ];
    }

    public function clear(): void
    {
        $this->cache->forget($this->getCacheKey());
    }

    protected function resolveMailAddress(mixed $notifiable): string
    {
        $mailAddress = match (true) {
            $notifiable instanceof AnonymousNotifiable => $notifiable->routeNotificationFor('mail'),
            is_object($notifiable) && method_exists($notifiable, 'routeNotificationFor') => $notifiable->routeNotificationFor('mail'),
            default => null,
        };

        if (! is_string($mailAddress) || $mailAddress === '') {
            throw new RuntimeException('Notifiable OTP harus memiliki alamat email yang valid.');
        }

        return $mailAddress;
    }

    protected function getCacheKey(): string
    {
        return $this->key.'_'.$this->identifier;
    }
}
