<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegisterOtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $code,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $logoPath = public_path('images/logo-email.png');

        return (new MailMessage)
            ->subject('Kode OTP Verifikasi Email '.config('app.name'))
            ->view('emails.auth.register-otp', [
                'appName' => config('app.name'),
                'code' => $this->code,
                'expiresInMinutes' => (int) config('otp.expires', 15),
                'logoUrl' => file_exists($logoPath) ? asset('images/logo-email.png') : null,
                'recipientEmail' => $this->resolveEmailAddress($notifiable),
            ]);
    }

    protected function resolveEmailAddress(object $notifiable): string
    {
        if (method_exists($notifiable, 'routeNotificationFor')) {
            return (string) $notifiable->routeNotificationFor('mail');
        }

        return (string) data_get($notifiable, 'email', '');
    }
}
