<x-app-layout title="Pengaturan Dashboard">
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-medium text-[var(--text-muted)]">Pengaturan chart dashboard</p>
            <h2 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">Pengaturan Dashboard</h2>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (! $hasStore)
                <x-card class="p-6">
                    <p class="text-sm font-semibold text-[var(--primary-ink)]">Profil toko belum lengkap</p>
                    <h3 class="mt-2 text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                        Lengkapi toko terlebih dahulu
                    </h3>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-[var(--text-muted)]">
                        Pengaturan chart dashboard membutuhkan konteks toko yang aktif. Lengkapi profil toko agar preset
                        dashboard dapat dibuat dan disimpan dengan benar.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('register.toko.create') }}" wire:navigate class="nyuci-btn-primary">
                            Lengkapi profil toko
                        </a>
                        <a href="{{ route('pengaturan-toko.edit') }}" wire:navigate class="nyuci-btn-secondary">
                            Buka pengaturan toko
                        </a>
                    </div>
                </x-card>
            @else
                <x-card class="p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-[var(--text-muted)]">Dashboard chart preset</p>
                            <h3 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">
                                Atur default toko dan preferensi pribadi
                            </h3>
                        </div>

                        <div class="text-sm text-[var(--text-muted)]">
                            4 slot tetap: hero, card_1, card_2, card_3
                        </div>
                    </div>
                </x-card>

                @if ($chartSettings)
                    <x-card class="p-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-[var(--text-muted)]">Default Toko</p>
                                <h3 class="text-xl font-semibold tracking-tight text-[var(--text-strong)]">
                                    Konfigurasi dasar untuk seluruh pengguna toko
                                </h3>
                            </div>

                            <p class="text-sm text-[var(--text-muted)]">
                                Perubahan di sini menjadi sumber utama dashboard.
                            </p>
                        </div>

                        <form method="post" action="{{ route('pengaturan-dashboard.defaults.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <div class="grid gap-4 xl:grid-cols-2">
                                @foreach ($chartSettings['slots'] as $slotKey => $slotData)
                                    <div class="rounded-[1.6rem] border border-[var(--border-main)] bg-[var(--bg-surface)] p-5">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">
                                                    {{ $slotData['definition']['label'] }}
                                                </p>
                                                <h4 class="mt-2 text-lg font-semibold tracking-tight text-[var(--text-strong)]">
                                                    {{ $slotData['preset']['title'] ?? $slotData['definition']['title'] }}
                                                </h4>
                                                <p class="mt-2 text-sm leading-6 text-[var(--text-main)]">
                                                    {{ $slotData['definition']['description'] }}
                                                </p>
                                            </div>

                                            <button
                                                type="submit"
                                                formaction="{{ route('pengaturan-dashboard.defaults.reset', $slotKey) }}"
                                                formmethod="post"
                                                class="nyuci-btn-secondary px-3 py-2 text-xs"
                                            >
                                                Reset
                                            </button>
                                        </div>

                                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                            <div class="sm:col-span-2">
                                                <x-input-label for="default-title-{{ $slotKey }}" :value="__('Judul')" />
                                                <x-text-input
                                                    id="default-title-{{ $slotKey }}"
                                                    name="charts[{{ $slotKey }}][title]"
                                                    type="text"
                                                    class="mt-1 block w-full"
                                                    :value="old('charts.'.$slotKey.'.title', $slotData['preset']['title'])"
                                                />
                                                <x-input-error class="mt-2" :messages="$errors->get('charts.'.$slotKey.'.title')" />
                                            </div>

                                            <div class="sm:col-span-2">
                                                <x-input-label for="default-subtitle-{{ $slotKey }}" :value="__('Subjudul')" />
                                                <x-text-input
                                                    id="default-subtitle-{{ $slotKey }}"
                                                    name="charts[{{ $slotKey }}][subtitle]"
                                                    type="text"
                                                    class="mt-1 block w-full"
                                                    :value="old('charts.'.$slotKey.'.subtitle', $slotData['preset']['subtitle'])"
                                                />
                                                <x-input-error class="mt-2" :messages="$errors->get('charts.'.$slotKey.'.subtitle')" />
                                            </div>

                                            <div>
                                                <x-input-label for="default-chart-type-{{ $slotKey }}" :value="__('Chart Type')" />
                                                <select
                                                    id="default-chart-type-{{ $slotKey }}"
                                                    name="charts[{{ $slotKey }}][chart_type]"
                                                    class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
                                                >
                                                    @foreach ($slotData['chart_type_options'] as $value => $label)
                                                        <option value="{{ $value }}" @selected(old('charts.'.$slotKey.'.chart_type', $slotData['preset']['chart_type']) === $value)>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <x-input-error class="mt-2" :messages="$errors->get('charts.'.$slotKey.'.chart_type')" />
                                            </div>

                                            <div>
                                                <x-input-label for="default-period-granularity-{{ $slotKey }}" :value="__('Granularity')" />
                                                <select
                                                    id="default-period-granularity-{{ $slotKey }}"
                                                    name="charts[{{ $slotKey }}][period_granularity]"
                                                    class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
                                                >
                                                    <option value="day" @selected(old('charts.'.$slotKey.'.period_granularity', $slotData['preset']['period_granularity']) === 'day')>Harian</option>
                                                    <option value="month" @selected(old('charts.'.$slotKey.'.period_granularity', $slotData['preset']['period_granularity']) === 'month')>Bulanan</option>
                                                </select>
                                                <x-input-error class="mt-2" :messages="$errors->get('charts.'.$slotKey.'.period_granularity')" />
                                            </div>

                                            <div>
                                                <x-input-label for="default-period-length-{{ $slotKey }}" :value="__('Periode')" />
                                                <select
                                                    id="default-period-length-{{ $slotKey }}"
                                                    name="charts[{{ $slotKey }}][period_length]"
                                                    class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
                                                >
                                                    <optgroup label="Harian">
                                                        @foreach (($chartSettings['period_length_options']['day'] ?? []) as $value => $label)
                                                            <option value="{{ $value }}" @selected((string) old('charts.'.$slotKey.'.period_length', $slotData['preset']['period_length']) === (string) $value)>
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                    <optgroup label="Bulanan">
                                                        @foreach (($chartSettings['period_length_options']['month'] ?? []) as $value => $label)
                                                            <option value="{{ $value }}" @selected((string) old('charts.'.$slotKey.'.period_length', $slotData['preset']['period_length']) === (string) $value)>
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                </select>
                                                <x-input-error class="mt-2" :messages="$errors->get('charts.'.$slotKey.'.period_length')" />
                                            </div>

                                            <div>
                                                <x-input-label for="default-primary-{{ $slotKey }}" :value="__('Metrik utama')" />
                                                <select
                                                    id="default-primary-{{ $slotKey }}"
                                                    name="charts[{{ $slotKey }}][primary_metric]"
                                                    class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
                                                >
                                                    @foreach ($chartSettings['metric_options'] as $value => $metric)
                                                        <option value="{{ $value }}" @selected(old('charts.'.$slotKey.'.primary_metric', $slotData['preset']['primary_metric']) === $value)>
                                                            {{ $metric['label'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <x-input-error class="mt-2" :messages="$errors->get('charts.'.$slotKey.'.primary_metric')" />
                                            </div>

                                            <div>
                                                <x-input-label for="default-secondary-{{ $slotKey }}" :value="__('Metrik sekunder')" />
                                                <select
                                                    id="default-secondary-{{ $slotKey }}"
                                                    name="charts[{{ $slotKey }}][secondary_metric]"
                                                    class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
                                                >
                                                    <option value="">Tidak ada</option>
                                                    @foreach ($chartSettings['metric_options'] as $value => $metric)
                                                        <option value="{{ $value }}" @selected(old('charts.'.$slotKey.'.secondary_metric', $slotData['preset']['secondary_metric']) === $value)>
                                                            {{ $metric['label'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <x-input-error class="mt-2" :messages="$errors->get('charts.'.$slotKey.'.secondary_metric')" />
                                            </div>

                                            <div>
                                                <x-input-label for="default-accent-{{ $slotKey }}" :value="__('Accent color')" />
                                                <select
                                                    id="default-accent-{{ $slotKey }}"
                                                    name="charts[{{ $slotKey }}][accent_color]"
                                                    class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
                                                >
                                                    @foreach ($chartSettings['color_options'] as $value => $label)
                                                        <option value="{{ $value }}" @selected(old('charts.'.$slotKey.'.accent_color', $slotData['preset']['accent_color']) === $value)>
                                                            {{ $label }} ({{ $value }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <x-input-error class="mt-2" :messages="$errors->get('charts.'.$slotKey.'.accent_color')" />
                                            </div>

                                            <div>
                                                <x-input-label for="default-show-points-{{ $slotKey }}" :value="__('Show points')" />
                                                <select
                                                    id="default-show-points-{{ $slotKey }}"
                                                    name="charts[{{ $slotKey }}][show_points]"
                                                    class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
                                                >
                                                    <option value="1" @selected((string) old('charts.'.$slotKey.'.show_points', (int) $slotData['preset']['show_points']) === '1')>Ya</option>
                                                    <option value="0" @selected((string) old('charts.'.$slotKey.'.show_points', (int) $slotData['preset']['show_points']) === '0')>Tidak</option>
                                                </select>
                                                <x-input-error class="mt-2" :messages="$errors->get('charts.'.$slotKey.'.show_points')" />
                                            </div>

                                            <div>
                                                <x-input-label for="default-show-previous-comparison-{{ $slotKey }}" :value="__('Tampilkan perbandingan vs sebelumnya')" />
                                                <select
                                                    id="default-show-previous-comparison-{{ $slotKey }}"
                                                    name="charts[{{ $slotKey }}][show_previous_comparison]"
                                                    class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
                                                >
                                                    @php($defaultShowPreviousComparison = (int) data_get($slotData, 'preset.show_previous_comparison', true))
                                                    <option value="1" @selected((string) old('charts.'.$slotKey.'.show_previous_comparison', $defaultShowPreviousComparison) === '1')>Ya</option>
                                                    <option value="0" @selected((string) old('charts.'.$slotKey.'.show_previous_comparison', $defaultShowPreviousComparison) === '0')>Tidak</option>
                                                </select>
                                                <x-input-error class="mt-2" :messages="$errors->get('charts.'.$slotKey.'.show_previous_comparison')" />
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Simpan Default') }}</x-primary-button>
                            </div>
                        </form>
                    </x-card>

                    <x-card class="p-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-[var(--text-muted)]">Preferensi Saya</p>
                                <h3 class="text-xl font-semibold tracking-tight text-[var(--text-strong)]">
                                    Override per user untuk slot yang dipilih
                                </h3>
                            </div>

                            <p class="text-sm text-[var(--text-muted)]">
                                Matikan toggle jika ingin kembali ke default toko.
                            </p>
                        </div>

                        <form method="post" action="{{ route('pengaturan-dashboard.overrides.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <div class="grid gap-4 xl:grid-cols-2">
                                @foreach ($chartSettings['slots'] as $slotKey => $slotData)
                                    <div
                                        x-data="{ custom: @js((bool) old('overrides.'.$slotKey.'.use_custom', data_get($slotData, 'override.use_custom', false))) }"
                                        class="rounded-[1.6rem] border border-[var(--border-main)] bg-[var(--bg-surface)] p-5"
                                    >
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">
                                                    {{ $slotData['definition']['label'] }}
                                                </p>
                                                <h4 class="mt-2 text-lg font-semibold tracking-tight text-[var(--text-strong)]">
                                                    {{ $slotData['effective']['title'] ?? $slotData['definition']['title'] }}
                                                </h4>
                                                <p class="mt-2 text-sm leading-6 text-[var(--text-main)]">
                                                    {{ $slotData['definition']['description'] }}
                                                </p>
                                            </div>

                                            <label class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.14em] text-[var(--text-muted)]">
                                                <input
                                                    type="checkbox"
                                                    name="overrides[{{ $slotKey }}][use_custom]"
                                                    value="1"
                                                    x-model="custom"
                                                    @checked(old('overrides.'.$slotKey.'.use_custom', data_get($slotData, 'override.use_custom', false)))
                                                    class="h-4 w-4 rounded border-[var(--border-soft)] text-[var(--primary)] focus:ring-[var(--primary)]"
                                                >
                                                Override untuk saya
                                            </label>
                                        </div>

                                        <div :class="custom ? '' : 'opacity-60'">
                                            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                                <div class="sm:col-span-2">
                                                    <x-input-label for="override-title-{{ $slotKey }}" :value="__('Judul')" />
                                                    <x-text-input
                                                        id="override-title-{{ $slotKey }}"
                                                        name="overrides[{{ $slotKey }}][title]"
                                                        type="text"
                                                        class="mt-1 block w-full"
                                                        :value="old('overrides.'.$slotKey.'.title', $slotData['effective']['title'])"
                                                        x-bind:disabled="!custom"
                                                    />
                                                    <x-input-error class="mt-2" :messages="$errors->get('overrides.'.$slotKey.'.title')" />
                                                </div>

                                                <div class="sm:col-span-2">
                                                    <x-input-label for="override-subtitle-{{ $slotKey }}" :value="__('Subjudul')" />
                                                    <x-text-input
                                                        id="override-subtitle-{{ $slotKey }}"
                                                        name="overrides[{{ $slotKey }}][subtitle]"
                                                        type="text"
                                                        class="mt-1 block w-full"
                                                        :value="old('overrides.'.$slotKey.'.subtitle', $slotData['effective']['subtitle'])"
                                                        x-bind:disabled="!custom"
                                                    />
                                                    <x-input-error class="mt-2" :messages="$errors->get('overrides.'.$slotKey.'.subtitle')" />
                                                </div>

                                                <div>
                                                    <x-input-label for="override-chart-type-{{ $slotKey }}" :value="__('Chart Type')" />
                                                    <select
                                                        id="override-chart-type-{{ $slotKey }}"
                                                        name="overrides[{{ $slotKey }}][chart_type]"
                                                        class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
                                                        x-bind:disabled="!custom"
                                                    >
                                                        @foreach ($slotData['chart_type_options'] as $value => $label)
                                                            <option value="{{ $value }}" @selected(old('overrides.'.$slotKey.'.chart_type', $slotData['effective']['chart_type']) === $value)>
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <x-input-error class="mt-2" :messages="$errors->get('overrides.'.$slotKey.'.chart_type')" />
                                                </div>

                                                <div>
                                                    <x-input-label for="override-period-granularity-{{ $slotKey }}" :value="__('Granularity')" />
                                                    <select
                                                        id="override-period-granularity-{{ $slotKey }}"
                                                        name="overrides[{{ $slotKey }}][period_granularity]"
                                                        class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
                                                        x-bind:disabled="!custom"
                                                    >
                                                        <option value="day" @selected(old('overrides.'.$slotKey.'.period_granularity', $slotData['effective']['period_granularity']) === 'day')>Harian</option>
                                                        <option value="month" @selected(old('overrides.'.$slotKey.'.period_granularity', $slotData['effective']['period_granularity']) === 'month')>Bulanan</option>
                                                    </select>
                                                    <x-input-error class="mt-2" :messages="$errors->get('overrides.'.$slotKey.'.period_granularity')" />
                                                </div>

                                                <div>
                                                    <x-input-label for="override-period-length-{{ $slotKey }}" :value="__('Periode')" />
                                                    <select
                                                        id="override-period-length-{{ $slotKey }}"
                                                        name="overrides[{{ $slotKey }}][period_length]"
                                                        class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
                                                        x-bind:disabled="!custom"
                                                    >
                                                        <optgroup label="Harian">
                                                            @foreach (($chartSettings['period_length_options']['day'] ?? []) as $value => $label)
                                                                <option value="{{ $value }}" @selected((string) old('overrides.'.$slotKey.'.period_length', $slotData['effective']['period_length']) === (string) $value)>
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                        <optgroup label="Bulanan">
                                                            @foreach (($chartSettings['period_length_options']['month'] ?? []) as $value => $label)
                                                                <option value="{{ $value }}" @selected((string) old('overrides.'.$slotKey.'.period_length', $slotData['effective']['period_length']) === (string) $value)>
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                    </select>
                                                    <x-input-error class="mt-2" :messages="$errors->get('overrides.'.$slotKey.'.period_length')" />
                                                </div>

                                                <div>
                                                    <x-input-label for="override-primary-{{ $slotKey }}" :value="__('Metrik utama')" />
                                                    <select
                                                        id="override-primary-{{ $slotKey }}"
                                                        name="overrides[{{ $slotKey }}][primary_metric]"
                                                        class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
                                                        x-bind:disabled="!custom"
                                                    >
                                                        @foreach ($chartSettings['metric_options'] as $value => $metric)
                                                            <option value="{{ $value }}" @selected(old('overrides.'.$slotKey.'.primary_metric', $slotData['effective']['primary_metric']) === $value)>
                                                                {{ $metric['label'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <x-input-error class="mt-2" :messages="$errors->get('overrides.'.$slotKey.'.primary_metric')" />
                                                </div>

                                                <div>
                                                    <x-input-label for="override-secondary-{{ $slotKey }}" :value="__('Metrik sekunder')" />
                                                    <select
                                                        id="override-secondary-{{ $slotKey }}"
                                                        name="overrides[{{ $slotKey }}][secondary_metric]"
                                                        class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
                                                        x-bind:disabled="!custom"
                                                    >
                                                        <option value="">Tidak ada</option>
                                                        @foreach ($chartSettings['metric_options'] as $value => $metric)
                                                            <option value="{{ $value }}" @selected(old('overrides.'.$slotKey.'.secondary_metric', $slotData['effective']['secondary_metric']) === $value)>
                                                                {{ $metric['label'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <x-input-error class="mt-2" :messages="$errors->get('overrides.'.$slotKey.'.secondary_metric')" />
                                                </div>

                                                <div>
                                                    <x-input-label for="override-accent-{{ $slotKey }}" :value="__('Accent color')" />
                                                    <select
                                                        id="override-accent-{{ $slotKey }}"
                                                        name="overrides[{{ $slotKey }}][accent_color]"
                                                        class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
                                                        x-bind:disabled="!custom"
                                                    >
                                                        @foreach ($chartSettings['color_options'] as $value => $label)
                                                            <option value="{{ $value }}" @selected(old('overrides.'.$slotKey.'.accent_color', $slotData['effective']['accent_color']) === $value)>
                                                                {{ $label }} ({{ $value }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <x-input-error class="mt-2" :messages="$errors->get('overrides.'.$slotKey.'.accent_color')" />
                                                </div>

                                                <div>
                                                    <x-input-label for="override-show-points-{{ $slotKey }}" :value="__('Show points')" />
                                                    <select
                                                        id="override-show-points-{{ $slotKey }}"
                                                        name="overrides[{{ $slotKey }}][show_points]"
                                                        class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
                                                        x-bind:disabled="!custom"
                                                    >
                                                        <option value="1" @selected((string) old('overrides.'.$slotKey.'.show_points', (int) $slotData['effective']['show_points']) === '1')>Ya</option>
                                                        <option value="0" @selected((string) old('overrides.'.$slotKey.'.show_points', (int) $slotData['effective']['show_points']) === '0')>Tidak</option>
                                                    </select>
                                                    <x-input-error class="mt-2" :messages="$errors->get('overrides.'.$slotKey.'.show_points')" />
                                                </div>

                                                <div>
                                                    <x-input-label for="override-show-previous-comparison-{{ $slotKey }}" :value="__('Tampilkan perbandingan vs sebelumnya')" />
                                                    <select
                                                        id="override-show-previous-comparison-{{ $slotKey }}"
                                                        name="overrides[{{ $slotKey }}][show_previous_comparison]"
                                                        class="mt-1 block w-full rounded-xl border border-[var(--border-soft)] bg-[var(--bg-card)] px-3 py-2 text-[var(--text-main)] shadow-sm focus:border-[var(--primary)] focus:ring-[var(--primary)]"
                                                        x-bind:disabled="!custom"
                                                    >
                                                        @php($defaultEffectiveShowPreviousComparison = (int) data_get($slotData, 'effective.show_previous_comparison', true))
                                                        <option value="1" @selected((string) old('overrides.'.$slotKey.'.show_previous_comparison', $defaultEffectiveShowPreviousComparison) === '1')>Ya</option>
                                                        <option value="0" @selected((string) old('overrides.'.$slotKey.'.show_previous_comparison', $defaultEffectiveShowPreviousComparison) === '0')>Tidak</option>
                                                    </select>
                                                    <x-input-error class="mt-2" :messages="$errors->get('overrides.'.$slotKey.'.show_previous_comparison')" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex items-center justify-between gap-3">
                                            <p class="text-xs text-[var(--text-muted)]">
                                                Toggle off = kembali pakai default toko
                                            </p>

                                            <button
                                                type="submit"
                                                formaction="{{ route('pengaturan-dashboard.overrides.destroy', $slotKey) }}"
                                                formmethod="post"
                                                name="_method"
                                                value="DELETE"
                                                class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--danger)]"
                                            >
                                                Hapus override
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Simpan Preferensi') }}</x-primary-button>
                            </div>
                        </form>
                    </x-card>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
