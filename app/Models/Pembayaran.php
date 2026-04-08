<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'klien_id',
        'laundry_id',
        'total_biaya',
        'total',
        'metode_pembayaran',
        'tgl_pembayaran',
        'catatan',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tgl_pembayaran' => 'date',
            'total' => 'integer',
            'total_biaya' => 'integer',
        ];
    }

    public function getMetodePembayaranLabelAttribute(): string
    {
        return match ($this->metode_pembayaran) {
            'cash' => 'Cash',
            'qris' => 'QRIS',
            'transfer' => 'Transfer',
            'ewallet' => 'E-Wallet',
            default => str($this->metode_pembayaran ?: '-')->replace('_', ' ')->title()->toString(),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'sudah_bayar' ? 'Sudah Bayar' : 'Belum Bayar';
    }

    public function getResolvedTotalAttribute(): int
    {
        return (int) ($this->total_biaya ?? $this->total ?? 0);
    }

    public function klien()
    {
        return $this->belongsTo(Klien::class);
    }

    public function laundry()
    {
        return $this->belongsTo(Laundry::class);
    }
}
