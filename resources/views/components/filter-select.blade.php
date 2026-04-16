@props([
    'options' => [],
    'placeholder' => 'Pilih opsi',
    'searchable' => false,
    'searchPlaceholder' => 'Cari pilihan...',
    'emptyMessage' => 'Tidak ada pilihan yang cocok.',
])

@php
    $wireModel = $attributes->wire('model');
    $wrapperClass = trim((string) $attributes->get('class'));
@endphp

<div
    x-data="nyuciFilterSelect({
        options: @js(array_values($options)),
        searchable: @js($searchable),
        searchPlaceholder: @js($searchPlaceholder),
        emptyMessage: @js($emptyMessage),
        selected: @entangle($wireModel),
    })"
    x-on:keydown.escape.window="close()"
    x-on:click.outside="close()"
    class="{{ trim('relative '.$wrapperClass) }}"
>
    <button
        type="button"
        x-on:click="toggle()"
        :aria-expanded="open"
        class="flex w-full items-center justify-between gap-3 rounded-xl border border-[var(--border-main)] bg-[var(--bg-surface)] px-4 py-3 text-left text-sm text-[var(--text-main)] transition hover:border-[var(--primary)] hover:bg-[var(--bg-card)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)]/20"
    >
        <span class="min-w-0 truncate font-medium" x-text="selectedLabel() || @js($placeholder)"></span>

        <svg class="h-4 w-4 shrink-0 text-[var(--text-muted)] transition" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0l-4.25-4.51a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
        </svg>
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition.origin.top.left
        class="absolute left-0 right-0 z-40 mt-2 overflow-hidden rounded-2xl border border-[var(--border-main)] bg-[var(--bg-card)] p-2 shadow-2xl shadow-black/10"
    >
        @if ($searchable)
            <div class="border-b border-[var(--border-main)] px-2 pb-2">
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[var(--text-muted)]">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.473 9.766l3.63 3.63a.75.75 0 1 0 1.06-1.06l-3.629-3.63A5.5 5.5 0 0 0 9 3.5Zm-4 5.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                        </svg>
                    </span>

                    <input
                        x-ref="search"
                        x-model="query"
                        type="text"
                        :placeholder="@js($searchPlaceholder)"
                        class="w-full rounded-xl border border-[var(--border-main)] bg-[var(--bg-surface)] py-2.5 pl-9 pr-3 text-sm text-[var(--text-main)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)]/20"
                    >
                </div>
            </div>
        @endif

        <div class="max-h-64 overflow-y-auto px-1 py-2">
            <template x-if="filteredOptions().length > 0">
                <div class="space-y-1">
                    <template x-for="option in filteredOptions()" :key="`${normalize(option.value)}-${option.label}`">
                        <button
                            type="button"
                            x-on:click="select(option)"
                            class="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-left text-sm transition"
                            :class="isSelected(option)
                                ? 'bg-[var(--primary-soft)] text-[var(--text-strong)]'
                                : 'text-[var(--text-main)] hover:bg-[var(--bg-surface)] hover:text-[var(--text-strong)]'"
                        >
                            <span class="min-w-0">
                                <span class="block truncate font-medium" x-text="option.label"></span>
                                <span x-show="option.meta" class="block truncate text-xs text-[var(--text-muted)]" x-text="option.meta"></span>
                            </span>

                            <svg x-show="isSelected(option)" class="h-4 w-4 shrink-0 text-[var(--primary)]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.072 7.143a1 1 0 0 1-1.42 0L4.29 9.92a1 1 0 1 1 1.414-1.414l3.224 3.223 6.365-6.43a1 1 0 0 1 1.41-.007Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </template>
                </div>
            </template>

            <template x-if="filteredOptions().length === 0">
                <div class="rounded-xl px-3 py-4 text-sm text-[var(--text-muted)]" x-text="@js($emptyMessage)"></div>
            </template>
        </div>
    </div>
</div>
