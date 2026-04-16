<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Toko extends Model
{
    use HasFactory;

    public const DEFAULT_DASHBOARD_CARDS = [
        'highlights' => true,
        'quick_actions' => true,
        'trend_chart' => true,
        'status_breakdown' => true,
        'payment_breakdown' => true,
        'top_services' => true,
        'recent_laundries' => true,
    ];

    public const DASHBOARD_CARD_OPTIONS = [
        'highlights' => [
            'label' => 'Ringkasan KPI',
            'description' => 'Tampilkan kartu total order, pendapatan bulan ini, order aktif, dan pelanggan aktif.',
        ],
        'quick_actions' => [
            'label' => 'Aksi cepat',
            'description' => 'Tampilkan tombol operasional cepat di sisi dashboard.',
        ],
        'trend_chart' => [
            'label' => 'Grafik tren order',
            'description' => 'Tampilkan line chart 14 hari terakhir untuk order masuk dan order selesai.',
        ],
        'status_breakdown' => [
            'label' => 'Ringkasan status order',
            'description' => 'Tampilkan distribusi order berdasarkan progres pengerjaan.',
        ],
        'payment_breakdown' => [
            'label' => 'Komposisi pembayaran',
            'description' => 'Tampilkan pembagian kanal pembayaran yang sudah tercatat.',
        ],
        'top_services' => [
            'label' => 'Layanan terlaris',
            'description' => 'Tampilkan layanan dengan kontribusi order tertinggi.',
        ],
        'recent_laundries' => [
            'label' => 'Laundry terbaru',
            'description' => 'Tampilkan aktivitas order terakhir dari toko Anda.',
        ],
    ];

    protected $fillable = [
        'user_id',
        'nama_toko',
        'alamat',
        'no_hp',
        'payment_gateway_qris_payload',
        'payment_gateway_qris_merchant_name',
        'payment_gateway_checkout_ttl_minutes',
        'background_mode',
        'background_color',
        'dashboard_cards',
    ];

    protected function casts(): array
    {
        return [
            'dashboard_cards' => 'array',
            'payment_gateway_checkout_ttl_minutes' => 'integer',
        ];
    }

    public function hasCustomPaymentGatewayQrisPayload(): bool
    {
        return filled(trim((string) $this->payment_gateway_qris_payload));
    }

    public function resolvedPaymentGatewayQrisPayload(): string
    {
        if ($this->hasCustomPaymentGatewayQrisPayload()) {
            return trim((string) $this->payment_gateway_qris_payload);
        }

        return trim((string) config('payment_gateway.qris_static.payload', ''));
    }

    public function resolvedPaymentGatewayQrisMerchantName(): ?string
    {
        $merchantName = trim((string) ($this->payment_gateway_qris_merchant_name ?? ''));

        if ($merchantName !== '') {
            return $merchantName;
        }

        $fallbackMerchantName = trim((string) config('payment_gateway.qris_static.merchant_name', ''));

        return $fallbackMerchantName !== '' ? $fallbackMerchantName : null;
    }

    public function resolvedPaymentGatewayCheckoutTtlMinutes(): int
    {
        $ttl = $this->payment_gateway_checkout_ttl_minutes;

        if (is_int($ttl) && $ttl > 0) {
            return $ttl;
        }

        return max((int) config('payment_gateway.checkout_ttl_minutes', 30), 1);
    }

    public function paymentGatewayQrisConfigSource(): string
    {
        return $this->hasCustomPaymentGatewayQrisPayload() ? 'toko' : 'server';
    }

    public function hasResolvedPaymentGatewayQrisConfig(): bool
    {
        return $this->resolvedPaymentGatewayQrisPayload() !== '';
    }

    public static function dashboardCardDefaults(): array
    {
        return self::DEFAULT_DASHBOARD_CARDS;
    }

    public static function dashboardCardOptions(): array
    {
        return self::DASHBOARD_CARD_OPTIONS;
    }

    public static function normalizeDashboardCards(?array $cards = null): array
    {
        $cards = $cards ?? [];

        return collect(self::dashboardCardDefaults())
            ->mapWithKeys(function (bool $default, string $key) use ($cards): array {
                $value = $cards[$key] ?? $default;

                if (is_string($value)) {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
                }

                return [$key => (bool) $value];
            })
            ->all();
    }

    public function dashboardCards(): array
    {
        return self::normalizeDashboardCards($this->dashboard_cards);
    }

    public function showsDashboardCard(string $key): bool
    {
        return $this->dashboardCards()[$key] ?? false;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function laundries()
    {
        return $this->hasMany(Laundry::class);
    }

    public function jasas()
    {
        return $this->hasMany(Jasa::class);
    }

    public function kliens()
    {
        return $this->hasMany(Klien::class);
    }

    public function dashboardChartPresets()
    {
        return $this->hasMany(DashboardChartPreset::class);
    }

    public function dashboardChartPreset(string $slotKey)
    {
        return $this->dashboardChartPresets()->where('slot_key', $slotKey);
    }

    public function dashboardChartUserOverrides()
    {
        return $this->hasManyThrough(
            DashboardChartUserOverride::class,
            DashboardChartPreset::class,
            'toko_id',
            'dashboard_chart_preset_id'
        );
    }
}
