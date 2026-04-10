<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Klien extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'toko_id',
        'nama_klien',
        'email_klien',
        'alamat_klien',
        'no_hp_klien',
    ];

    public function routeNotificationForMail(?object $notification = null): ?string
    {
        return $this->email_klien;
    }

    public function toko()
    {
        return $this->belongsTo(Toko::class);
    }

    public function laundries()
    {
        return $this->hasMany(Laundry::class);
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }
}
