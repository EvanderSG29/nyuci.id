@php
    $lastOrder = filled($klien->terakhir_order)
        ? \Illuminate\Support\Carbon::parse($klien->terakhir_order)->translatedFormat('d M Y')
        : '-';
@endphp

<x-detail-preview
    :title="$klien->nama_klien"
    :subtitle="$klien->no_hp_klien"
>
    <x-detail-preview-item label="No. HP" :value="$klien->no_hp_klien" />

    @if (filled($klien->email_klien))
        <x-detail-preview-item label="Email" :value="$klien->email_klien" />
    @endif

    <x-detail-preview-item label="Alamat" :value="$klien->alamat_klien ?: '-'" />
    <x-detail-preview-item label="Total order" :value="$klien->total_order.' order'" />
    <x-detail-preview-item label="Belum bayar">
        <span class="nyuci-badge {{ $klien->belum_bayar > 0 ? 'nyuci-badge-pending' : 'nyuci-badge-success' }}">
            {{ $klien->belum_bayar }} tagihan
        </span>
    </x-detail-preview-item>
    <x-detail-preview-item label="Terakhir order" :value="$lastOrder" />
</x-detail-preview>
