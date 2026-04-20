@php
    $paymentActive = in_array($settingsSection, ['payment-qris', 'payment-methods'], true);
    $navItems = [
        ['key' => 'profile', 'label' => 'Profil', 'route' => 'settings.profile'],
        ['key' => 'store', 'label' => 'Toko', 'route' => 'settings.toko'],
        ['key' => 'personalization', 'label' => 'Personalisasi', 'route' => 'settings.personalisasi'],
    ];
@endphp

<x-app-layout :title="$settingsTitle" navbar-eyebrow="Pengaturan utama">
    <div class="py-8 sm:py-10">
        <div
            class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"
            x-data="settingsGuard({
                saveSucceeded: @js($settingsSaveSucceeded),
            })"
        >
            <div class="nyuci-settings-layout">
                <aside class="nyuci-settings-sidebar">
                    <div class="border-b border-[var(--border-main)] pb-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--text-muted)]">Settings</p>
                        <h3 class="mt-2 text-lg font-semibold text-[var(--text-strong)]">Pengaturan Utama</h3>
                        <p class="mt-2 text-sm leading-6 text-[var(--text-muted)]">
                            Susun akun, toko, pembayaran, dan dashboard dalam satu area kerja yang rapi.
                        </p>
                    </div>

                    <nav class="mt-4 space-y-1">
                        @foreach ($navItems as $item)
                            @php($isActive = $settingsSection === $item['key'])
                            <a
                                href="{{ route($item['route']) }}"
                                wire:navigate
                                class="nyuci-settings-nav-link {{ $isActive ? 'is-active' : '' }}"
                            >
                                <span class="nyuci-settings-nav-icon">
                                    @switch($item['key'])
                                        @case('profile')
                                            <flux:icon.user-circle class="size-4" />
                                            @break
                                        @case('store')
                                            <flux:icon.building-storefront class="size-4" />
                                            @break
                                        @case('personalization')
                                            <flux:icon.swatch class="size-4" />
                                            @break
                                    @endswitch
                                </span>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach

                        <div class="pt-2">
                            <div class="nyuci-settings-nav-parent {{ $paymentActive ? 'is-active' : '' }}">
                                <span class="nyuci-settings-nav-icon">
                                    <flux:icon.credit-card class="size-4" />
                                </span>
                                <span>Pembayaran</span>
                            </div>

                            <div class="mt-2 space-y-1 pl-4">
                                <a
                                    href="{{ route('settings.payment.qris') }}"
                                    wire:navigate
                                    class="nyuci-settings-nav-link is-child {{ $settingsSection === 'payment-qris' ? 'is-active' : '' }}"
                                >
                                    <span>QRIS</span>
                                </a>

                                <a
                                    href="{{ route('settings.payment.methods') }}"
                                    wire:navigate
                                    class="nyuci-settings-nav-link is-child {{ $settingsSection === 'payment-methods' ? 'is-active' : '' }}"
                                >
                                    <span>Metode Lainnya</span>
                                </a>
                            </div>
                        </div>

                        <a
                            href="{{ route('settings.dashboard') }}"
                            wire:navigate
                            class="nyuci-settings-nav-link {{ $settingsSection === 'dashboard' ? 'is-active' : '' }}"
                        >
                            <span class="nyuci-settings-nav-icon">
                                <flux:icon.chart-bar class="size-4" />
                            </span>
                            <span>Dashboard</span>
                        </a>
                    </nav>
                </aside>

                <div class="min-w-0 space-y-6">
                    <x-card class="p-6 sm:p-7">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--primary-ink)]">Subsection aktif</p>
                        <h3 class="mt-2 text-2xl font-semibold tracking-tight text-[var(--text-strong)]">{{ $settingsTitle }}</h3>
                        <p class="mt-3 max-w-3xl text-sm leading-7 text-[var(--text-muted)]">
                            {{ $settingsDescription }}
                        </p>
                    </x-card>

                    @include('settings.sections.'.$settingsSection)
                </div>
            </div>

            <x-modal name="settings-unsaved-changes" :show="false" maxWidth="lg" focusable>
                <div class="p-6">
                    <h2 class="text-lg font-medium text-[var(--text-strong)]">
                        Perubahan belum disimpan
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-[var(--text-muted)]">
                        Ada perubahan di halaman ini yang belum disimpan. Pilih tindakan sebelum Anda meninggalkan pengaturan.
                    </p>

                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <x-secondary-button
                            type="button"
                            x-on:click="$dispatch('close-modal', 'settings-unsaved-changes'); stayOnPage()"
                        >
                            Tetap di Halaman
                        </x-secondary-button>

                        <x-secondary-button
                            type="button"
                            x-on:click="$dispatch('close-modal', 'settings-unsaved-changes'); discardAndLeave()"
                        >
                            Keluar Tanpa Menyimpan
                        </x-secondary-button>

                        <x-primary-button
                            type="button"
                            x-on:click="$dispatch('close-modal', 'settings-unsaved-changes'); saveAndLeave()"
                        >
                            Simpan dan Keluar
                        </x-primary-button>
                    </div>
                </div>
            </x-modal>
        </div>
    </div>
</x-app-layout>
