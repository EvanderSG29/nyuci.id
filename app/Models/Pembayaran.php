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
        'gateway_provider',
        'gateway_reference',
        'gateway_invoice_id',
        'gateway_token',
        'gateway_payment_url',
        'gateway_qr_image',
        'gateway_request_date',
        'gateway_expires_at',
        'gateway_status',
        'gateway_customer_name',
        'gateway_method_by',
        'gateway_paid_at',
        'gateway_payload',
    ];

    protected function casts(): array
    {
        return [
            'tgl_pembayaran' => 'date',
            'total' => 'integer',
            'total_biaya' => 'integer',
            'gateway_request_date' => 'date',
            'gateway_expires_at' => 'datetime',
            'gateway_paid_at' => 'datetime',
            'gateway_payload' => 'array',
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

    public function getGatewayStatusLabelAttribute(): string
    {
        return match ($this->gateway_status ?: ($this->gateway_token ? 'pending' : null)) {
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Lunas',
            'expired' => 'Kedaluwarsa',
            'error' => 'Gagal',
            default => 'Belum Dibuat',
        };
    }

    public function getGatewayStatusVariantAttribute(): string
    {
        return match ($this->gateway_status ?: ($this->gateway_token ? 'pending' : null)) {
            'paid' => 'paid',
            'expired', 'error' => 'danger',
            'pending' => 'pending',
            default => 'default',
        };
    }

    public function getGatewayCheckoutUrlAttribute(): ?string
    {
        return $this->gateway_token
            ? route('pembayaran.gateway.checkout', [$this, $this->gateway_token])
            : null;
    }

    public function getGatewaySyncUrlAttribute(): ?string
    {
        return $this->gateway_token
            ? route('pembayaran.gateway.sync', [$this, $this->gateway_token])
            : null;
    }

    public function getResolvedTotalAttribute(): int
    {
        return (int) ($this->total_biaya ?? $this->total ?? 0);
    }

    public function gatewayHasSession(): bool
    {
        return filled($this->gateway_token)
            && filled($this->gateway_invoice_id)
            && filled($this->gateway_request_date)
            && filled($this->gateway_expires_at);
    }

    public function gatewaySessionIsActive(): bool
    {
        return $this->gatewayHasSession()
            && ($this->gateway_status === null || $this->gateway_status === 'pending')
            && $this->gateway_expires_at?->isFuture();
    }

    public function setGatewaySession(array $session): void
    {
        $this->forceFill([
            'gateway_provider' => $session['provider'] ?? 'qris_static',
            'gateway_reference' => $session['reference'] ?? null,
            'gateway_invoice_id' => $session['invoice_id'] ?? null,
            'gateway_token' => $session['token'] ?? null,
            'gateway_payment_url' => $session['payment_url'] ?? null,
            'gateway_qr_image' => $session['qr_image'] ?? null,
            'gateway_request_date' => $session['request_date'] ?? null,
            'gateway_expires_at' => $session['expires_at'] ?? null,
            'gateway_status' => $session['status'] ?? 'pending',
            'gateway_customer_name' => $session['customer_name'] ?? null,
            'gateway_method_by' => $session['method_by'] ?? null,
            'gateway_payload' => $session['payload'] ?? null,
        ])->save();
    }

    public function syncGatewayPayment(array $result): void
    {
        $updates = [
            'gateway_status' => $result['status'] ?? 'pending',
            'gateway_customer_name' => $result['customer_name'] ?? $this->gateway_customer_name,
            'gateway_method_by' => $result['method_by'] ?? $this->gateway_method_by,
            'gateway_payload' => $result['payload'] ?? $this->gateway_payload,
        ];

        if (($result['status'] ?? null) === 'paid') {
            $updates['status'] = 'sudah_bayar';
            $updates['metode_pembayaran'] = 'qris';
            $updates['tgl_pembayaran'] = $this->tgl_pembayaran ?? now()->toDateString();
            $updates['gateway_paid_at'] = $this->gateway_paid_at ?? now();
        } elseif (($result['status'] ?? null) === 'expired') {
            $updates['gateway_status'] = 'expired';
        }

        $this->forceFill($updates)->save();
    }

    public function clearGatewaySession(): void
    {
        $this->forceFill([
            'gateway_provider' => null,
            'gateway_reference' => null,
            'gateway_invoice_id' => null,
            'gateway_payment_url' => null,
            'gateway_qr_image' => null,
            'gateway_request_date' => null,
            'gateway_expires_at' => null,
            'gateway_status' => 'expired',
            'gateway_customer_name' => null,
            'gateway_method_by' => null,
            'gateway_paid_at' => null,
            'gateway_payload' => null,
        ])->save();
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
