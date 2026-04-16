@php
    $customerName = $laundry->klien?->nama_klien ?? $laundry->nama ?? '-';
    $customerPhone = $laundry->klien?->no_hp_klien ?? $laundry->no_hp ?? '-';
    $serviceName = $laundry->jasa?->nama_jasa ?? $laundry->jenis_jasa_label;
    $paymentStatus = $laundry->pembayaran?->status_label ?? 'Belum Bayar';
    $paymentVariant = $laundry->pembayaran?->status === 'sudah_bayar' ? 'nyuci-badge-success' : 'nyuci-badge-pending';
    $statusVariant = match ($laundry->status) {
        'selesai' => 'nyuci-badge-success',
        'proses' => 'nyuci-badge-default',
        default => 'nyuci-badge-pending',
    };
    $estimatedTotal = $laundry->pembayaran?->resolved_total ?? (int) round(($laundry->qty ?? 0) * ($laundry->jasa?->harga ?? 0));
@endphp

<x-detail-preview
    :title="$customerName"
    :subtitle="$serviceName.' • '.$laundry->satuan_label"
>
    <x-detail-preview-item label="Pelanggan" :value="$customerName" />
    <x-detail-preview-item label="Kontak" :value="$customerPhone" />
    <x-detail-preview-item label="Qty / Satuan" :value="$laundry->formatted_qty.' • '.$laundry->satuan_label" />
    <x-detail-preview-item label="Status laundry">
        <span class="nyuci-badge {{ $statusVariant }}">{{ $laundry->status_label }}</span>
    </x-detail-preview-item>
    <x-detail-preview-item label="Status pembayaran">
        <span class="nyuci-badge {{ $paymentVariant }}">{{ $paymentStatus }}</span>
    </x-detail-preview-item>
    <x-detail-preview-item label="Tanggal masuk" :value="$laundry->tanggal_dimulai?->translatedFormat('d M Y') ?? '-'" />
    <x-detail-preview-item label="ETS" :value="$laundry->ets_selesai?->translatedFormat('d M Y') ?? '-'" />

    @if ($laundry->tgl_selesai)
        <x-detail-preview-item label="Tanggal selesai" :value="$laundry->tgl_selesai->translatedFormat('d M Y')" />
    @endif

    <x-detail-preview-item label="Estimasi total" :value="'Rp '.number_format($estimatedTotal, 0, ',', '.')" emphasis />
</x-detail-preview>
