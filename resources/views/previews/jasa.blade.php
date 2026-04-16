<x-detail-preview
    :title="$jasa->nama_jasa"
    :subtitle="'Layanan '.$jasa->satuan.' yang aktif di toko ini.'"
>
    <x-detail-preview-item label="Harga" :value="'Rp '.number_format((int) $jasa->harga, 0, ',', '.')" emphasis />
    <x-detail-preview-item label="Satuan" :value="$jasa->satuan" />
    <x-detail-preview-item label="Total order" :value="$jasa->total_order.' order'" />

    @if ($jasa->created_at)
        <x-detail-preview-item label="Dibuat" :value="$jasa->created_at->translatedFormat('d M Y')" />
    @endif

    @if ($jasa->updated_at)
        <x-detail-preview-item label="Diperbarui" :value="$jasa->updated_at->translatedFormat('d M Y')" />
    @endif
</x-detail-preview>
