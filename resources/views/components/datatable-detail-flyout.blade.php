@props(['name'])

<flux:modal :name="$name" flyout class="nyuci-detail-flyout">
    <div class="nyuci-detail-flyout-shell">
        <div class="nyuci-detail-flyout-body" data-dt-flyout-body>
            <div class="nyuci-detail-state">
                <p class="nyuci-detail-state-label">Detail informasi</p>
                <h3 class="nyuci-detail-state-title">Pilih data dari tabel</h3>
                <p class="nyuci-detail-state-text">Klik aksi Detail pada salah satu baris untuk melihat ringkasan dari sisi kanan.</p>
            </div>
        </div>
    </div>
</flux:modal>
