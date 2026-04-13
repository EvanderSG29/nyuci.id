@php
    $customerName = $pembayaran->klien?->nama_klien ?? $pembayaran->laundry?->klien?->nama_klien ?? $pembayaran->laundry?->nama ?? '-';
    $serviceName = $pembayaran->laundry?->jasa?->nama_jasa ?? $pembayaran->laundry?->jenis_jasa_label ?? '-';
    $statusVariant = $pembayaran->status === 'sudah_bayar' ? 'nyuci-badge-success' : 'nyuci-badge-pending';
    $gatewayVariant = match ($pembayaran->gateway_status_variant) {
        'paid' => 'success',
        'danger' => 'danger',
        'pending' => 'pending',
        default => 'default',
    };
@endphp

<x-detail-preview
    :title="'Pembayaran #'.$pembayaran->id"
    :subtitle="$customerName.' • '.$serviceName"
>
    <x-detail-preview-item label="Pelanggan" :value="$customerName" />
    <x-detail-preview-item label="Layanan" :value="$serviceName" />
    <x-detail-preview-item label="Total" :value="'Rp '.number_format($pembayaran->resolved_total, 0, ',', '.')" emphasis />
    <x-detail-preview-item label="Status pembayaran">
        <span class="nyuci-badge {{ $statusVariant }}">{{ $pembayaran->status_label }}</span>
    </x-detail-preview-item>
    <x-detail-preview-item label="Metode" :value="$pembayaran->metode_pembayaran_label" />
    <x-detail-preview-item label="Tanggal bayar" :value="$pembayaran->tgl_pembayaran?->translatedFormat('d M Y') ?? '-'" />

    @if ($pembayaran->gateway_token || $pembayaran->gateway_status)
        <x-detail-preview-item label="Status gateway">
            <span class="nyuci-badge nyuci-badge-{{ $gatewayVariant }}">
                {{ $pembayaran->gateway_status_label }}
            </span>
        </x-detail-preview-item>
    @endif

    @if (filled($pembayaran->catatan))
        <x-detail-preview-item label="Catatan" :value="$pembayaran->catatan" />
    @endif
</x-detail-preview>
