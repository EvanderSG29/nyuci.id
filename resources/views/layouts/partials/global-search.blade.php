@php
    $searchEnabled = $searchEnabled ?? false;
@endphp

<label class="nyuci-dashboard-search">
    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M21 21l-4.2-4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
        <circle cx="11" cy="11" r="6.2" stroke="currentColor" stroke-width="1.8" />
    </svg>
    <input
        type="search"
        x-model.debounce.250ms="query"
        @focus="openSearch()"
        placeholder="{{ $searchEnabled ? 'Cari laundry, pelanggan, pembayaran...' : 'Lengkapi toko untuk mengaktifkan pencarian dashboard' }}"
        autocomplete="off"
        @disabled(! $searchEnabled)
    >
    <button
        type="button"
        x-cloak
        x-show="query !== ''"
        @click.prevent="clearSearch()"
        class="nyuci-dashboard-search-clear"
        aria-label="Hapus pencarian"
    >
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
        </svg>
    </button>
</label>

@unless ($searchEnabled)
    <p class="nyuci-dashboard-nav-helper mt-2 hidden text-xs sm:block">
        Search aktif setelah profil toko dilengkapi.
    </p>
@endunless

<div
    x-cloak
    x-show="shouldShowSearch()"
    x-transition.opacity.scale.origin.top
    class="nyuci-dashboard-search-panel"
>
    <div x-show="loading" class="nyuci-dashboard-search-state">
        Mencari data terbaru...
    </div>

    <div x-show="error" x-text="error" class="nyuci-dashboard-search-state is-error"></div>

    <div x-show="!loading && !error && !hasSearchResults()" class="nyuci-dashboard-search-state">
        Tidak ada hasil untuk pencarian ini.
    </div>

    <template x-for="group in results.groups" :key="group.key">
        <section class="nyuci-dashboard-search-group">
            <div class="flex items-center justify-between gap-3 px-4 py-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]" x-text="group.label"></p>
                </div>

                <a
                    class="text-xs font-semibold text-[var(--primary)] transition hover:text-[var(--primary-hover)]"
                    x-bind:href="group.index_url"
                >
                    Lihat semua
                </a>
            </div>

            <div class="px-2 pb-2">
                <template x-for="item in group.items" :key="item.url">
                    <a x-bind:href="item.url" class="nyuci-dashboard-search-item">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-[var(--text-strong)]" x-text="item.title"></p>
                            <p class="mt-1 truncate text-xs text-[var(--text-main)]" x-text="item.subtitle"></p>
                        </div>

                        <p class="shrink-0 text-[0.7rem] font-medium uppercase tracking-[0.14em] text-[var(--text-muted)]" x-text="item.meta"></p>
                    </a>
                </template>
            </div>
        </section>
    </template>
</div>
