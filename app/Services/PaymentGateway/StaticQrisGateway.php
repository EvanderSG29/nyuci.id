<?php

namespace App\Services\PaymentGateway;

use App\Models\Pembayaran;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use RuntimeException;

class StaticQrisGateway
{
    public function issue(Pembayaran $pembayaran): array
    {
        if ($pembayaran->gateway_status === 'paid' && $pembayaran->gatewayHasSession()) {
            return ['created' => false, ...$this->snapshot($pembayaran)];
        }

        if ($pembayaran->gatewaySessionIsActive()) {
            return ['created' => false, ...$this->snapshot($pembayaran)];
        }

        $this->ensureConfigured();

        $staticPayload = $this->staticPayload();
        $reference = $this->buildReference($pembayaran);
        $invoiceId = $this->buildInvoiceId($pembayaran);
        $qrisText = $this->buildDynamicPayload($staticPayload, (int) $pembayaran->resolved_total);
        $merchantName = $this->merchantNameFromPayload($staticPayload);
        $qrImage = $this->renderQrImageDataUri($qrisText);
        $requestDate = CarbonImmutable::now();
        $expiresAt = $requestDate->addMinutes($this->ttlMinutes());

        return [
            'created' => true,
            'provider' => $this->driverName(),
            'reference' => $reference,
            'invoice_id' => $invoiceId,
            'token' => Str::random(64),
            'payment_url' => null,
            'qr_image' => $qrImage,
            'request_date' => $requestDate->toDateString(),
            'expires_at' => $expiresAt,
            'status' => 'pending',
            'customer_name' => null,
            'method_by' => null,
            'payload' => [
                'static_payload' => $staticPayload,
                'qris_text' => $qrisText,
                'merchant_name' => $merchantName,
                'amount' => (int) $pembayaran->resolved_total,
            ],
            'qris_text' => $qrisText,
            'merchant_name' => $merchantName,
        ];
    }

    public function check(Pembayaran $pembayaran): array
    {
        if ($pembayaran->status === 'sudah_bayar' || $pembayaran->gateway_status === 'paid') {
            $customerName = $pembayaran->gateway_customer_name
                ?? $pembayaran->klien?->nama_klien
                ?? $pembayaran->laundry?->nama
                ?? 'Customer';

            return [
                'status' => 'paid',
                'is_paid' => true,
                'customer_name' => $customerName,
                'method_by' => $pembayaran->gateway_method_by ?? 'QRIS Statis',
                'payload' => $pembayaran->gateway_payload ?? [],
                'message' => 'Pembayaran sudah lunas.',
            ];
        }

        if (! $pembayaran->gatewayHasSession()) {
            throw new RuntimeException('Sesi pembayaran QRIS belum dibuat.');
        }

        if ($this->isExpired($pembayaran)) {
            return [
                'status' => 'expired',
                'is_paid' => false,
                'customer_name' => $pembayaran->gateway_customer_name,
                'method_by' => $pembayaran->gateway_method_by ?? 'QRIS Statis',
                'payload' => $pembayaran->gateway_payload ?? [],
                'message' => 'Sesi pembayaran sudah kedaluwarsa.',
            ];
        }

        return [
            'status' => 'pending',
            'is_paid' => false,
            'customer_name' => $pembayaran->gateway_customer_name,
            'method_by' => $pembayaran->gateway_method_by ?? 'QRIS Statis',
            'payload' => $pembayaran->gateway_payload ?? [],
            'message' => 'Pembayaran belum dikonfirmasi.',
        ];
    }

    public function snapshot(Pembayaran $pembayaran): array
    {
        $payload = $pembayaran->gateway_payload ?? [];
        $qrisText = data_get($payload, 'qris_text');
        $qrImage = $pembayaran->gateway_qr_image;

        if (blank($qrImage) && is_string($qrisText) && trim($qrisText) !== '') {
            $qrImage = $this->renderQrImageDataUri($qrisText);
        }

        return [
            'provider' => $pembayaran->gateway_provider ?? $this->driverName(),
            'reference' => $pembayaran->gateway_reference,
            'invoice_id' => $pembayaran->gateway_invoice_id,
            'token' => $pembayaran->gateway_token,
            'payment_url' => $pembayaran->gateway_payment_url,
            'qr_image' => $qrImage,
            'request_date' => $pembayaran->gateway_request_date?->toDateString(),
            'expires_at' => $pembayaran->gateway_expires_at,
            'status' => $this->resolveSessionStatus($pembayaran),
            'customer_name' => $pembayaran->gateway_customer_name,
            'method_by' => $pembayaran->gateway_method_by,
            'payload' => $payload,
            'qris_text' => $qrisText,
            'merchant_name' => data_get($payload, 'merchant_name'),
        ];
    }

    public function hasReusableSession(Pembayaran $pembayaran): bool
    {
        return $pembayaran->gatewaySessionIsActive();
    }

    private function resolveSessionStatus(Pembayaran $pembayaran): string
    {
        if ($pembayaran->gateway_status === 'paid' || $pembayaran->status === 'sudah_bayar') {
            return 'paid';
        }

        if ($pembayaran->gateway_status === 'expired' || $this->isExpired($pembayaran)) {
            return 'expired';
        }

        return 'pending';
    }

    private function isExpired(Pembayaran $pembayaran): bool
    {
        return $pembayaran->gateway_expires_at?->isPast() ?? false;
    }

    private function buildReference(Pembayaran $pembayaran): string
    {
        return sprintf('NYUCI-%s-%s', $pembayaran->id, Str::upper(Str::random(10)));
    }

    private function buildInvoiceId(Pembayaran $pembayaran): string
    {
        return sprintf('INV-%s-%s', $pembayaran->id, Str::upper(Str::random(8)));
    }

    private function ensureConfigured(): void
    {
        if ($this->staticPayload() === '') {
            throw new RuntimeException('QRIS statis belum dikonfigurasi di file environment. Isi PAYMENT_GATEWAY_QRIS_STATIC_PAYLOAD.');
        }
    }

    private function driverName(): string
    {
        return (string) config('payment_gateway.driver', 'qris_static');
    }

    private function staticPayload(): string
    {
        return trim((string) config('payment_gateway.qris_static.payload', ''));
    }

    private function merchantNameFromPayload(string $payload): string
    {
        $configuredMerchant = trim((string) config('payment_gateway.qris_static.merchant_name', ''));

        if ($configuredMerchant !== '') {
            return $configuredMerchant;
        }

        $merchantName = $this->parseQrisTag($payload, '59');

        return is_string($merchantName) && trim($merchantName) !== ''
            ? $merchantName
            : 'QRIS Statis';
    }

    private function ttlMinutes(): int
    {
        return max((int) config('payment_gateway.checkout_ttl_minutes', 30), 1);
    }

    private function buildDynamicPayload(string $staticPayload, int $amount): string
    {
        $payload = trim($staticPayload);

        if (strlen($payload) < 8) {
            throw new RuntimeException('Payload QRIS statis tidak valid.');
        }

        $payloadWithoutCrc = substr($payload, 0, -4);

        if (str_contains($payloadWithoutCrc, '010211')) {
            $payloadWithoutCrc = str_replace('010211', '010212', $payloadWithoutCrc);
        }

        if (! str_contains($payloadWithoutCrc, '5802ID')) {
            throw new RuntimeException('Format QRIS statis tidak dikenali.');
        }

        $amountValue = (string) max($amount, 0);
        $amountField = '54'.str_pad((string) strlen($amountValue), 2, '0', STR_PAD_LEFT).$amountValue;
        [$prefix, $suffix] = explode('5802ID', $payloadWithoutCrc, 2);
        $finalPayload = $prefix.$amountField.'5802ID'.$suffix;

        return $finalPayload.$this->crc16($finalPayload);
    }

    private function renderQrImageDataUri(string $content): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(320, 4),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $svg = $writer->writeString($content);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    private function parseQrisTag(string $qrisString, string $tag): ?string
    {
        $index = 0;
        $length = strlen($qrisString);

        while ($index < $length) {
            $currentTag = substr($qrisString, $index, 2);
            $index += 2;

            if ($index + 2 > $length) {
                break;
            }

            $tagLength = (int) substr($qrisString, $index, 2);
            $index += 2;

            if ($tagLength <= 0 || $index + $tagLength > $length) {
                break;
            }

            $value = substr($qrisString, $index, $tagLength);

            if ($currentTag === $tag) {
                return $value;
            }

            $index += $tagLength;
        }

        return null;
    }

    private function crc16(string $str): string
    {
        $crc = 0xFFFF;
        $length = strlen($str);

        for ($i = 0; $i < $length; $i++) {
            $crc ^= ord($str[$i]) << 8;

            for ($j = 0; $j < 8; $j++) {
                if (($crc & 0x8000) !== 0) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc <<= 1;
                }
            }
        }

        return strtoupper(str_pad(dechex($crc & 0xFFFF), 4, '0', STR_PAD_LEFT));
    }
}
