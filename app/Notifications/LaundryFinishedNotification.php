<?php

namespace App\Notifications;

use App\Models\Klien;
use App\Models\Laundry;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LaundryFinishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Laundry $laundry,
    ) {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable instanceof User) {
            $channels[] = 'database';

            if ($this->shouldBroadcast()) {
                $channels[] = 'broadcast';
            }
        }

        if ($this->shouldSendMail($notifiable)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function viaQueues(): array
    {
        return [
            'database' => 'notifications',
            'broadcast' => 'broadcasts',
            'mail' => 'mail',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $laundry = $this->laundryDetails();
        $finishedAt = $laundry->tgl_selesai?->translatedFormat('d M Y') ?? now()->translatedFormat('d M Y');

        return (new MailMessage)
            ->subject('Laundry selesai: '.$laundry->nama)
            ->greeting('Halo '.$laundry->nama.',')
            ->line('Pesanan laundry Anda di '.$laundry->toko?->nama_toko.' sudah selesai diproses.')
            ->line('Layanan: '.$laundry->jenis_jasa_label.' ('.$laundry->satuan_label.')')
            ->line('Tanggal selesai: '.$finishedAt)
            ->line('Silakan hubungi toko untuk konfirmasi pengambilan atau penyerahan.')
            ->salutation('Terima kasih, '.config('app.name'));
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->payload();
    }

    public function toArray(object $notifiable): array
    {
        return $this->payload();
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload());
    }

    protected function payload(): array
    {
        $laundry = $this->laundryDetails();

        return [
            'type' => 'laundry_finished',
            'title' => 'Laundry selesai diproses',
            'message' => sprintf(
                'Pesanan %s untuk %s sudah selesai dan siap diambil.',
                $laundry->jenis_jasa_label,
                $laundry->nama
            ),
            'icon' => 'truck',
            'laundry_id' => $laundry->getKey(),
            'customer_name' => $laundry->nama,
            'customer_phone' => $laundry->no_hp,
            'service_name' => $laundry->jenis_jasa_label,
            'quantity_label' => $laundry->satuan_label,
            'finished_at' => $laundry->tgl_selesai?->toIso8601String(),
            'action_url' => route('laundry.index'),
            'action_label' => 'Buka daftar laundry',
        ];
    }

    protected function laundryDetails(): Laundry
    {
        return $this->laundry->loadMissing('klien', 'jasa', 'toko');
    }

    protected function shouldBroadcast(): bool
    {
        return (bool) config('notifications.broadcast_enabled', false);
    }

    protected function shouldSendMail(object $notifiable): bool
    {
        if ($notifiable instanceof User) {
            return false;
        }

        if ($notifiable instanceof Klien) {
            return filled($notifiable->email_klien);
        }

        if (method_exists($notifiable, 'routeNotificationFor')) {
            return filled($notifiable->routeNotificationFor('mail', $this));
        }

        return filled(data_get($notifiable, 'email'));
    }
}
