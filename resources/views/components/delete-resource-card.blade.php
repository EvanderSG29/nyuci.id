@props([
    'action',
    'modalName',
    'title' => 'Opsi Hapus Data',
    'description' => 'Data ini bisa dihapus jika sudah tidak diperlukan lagi.',
    'triggerLabel' => 'Hapus Data',
    'modalTitle' => 'Konfirmasi Hapus Data',
    'modalDescription' => 'Tindakan ini akan menghapus data secara permanen. Pastikan Anda sudah memeriksa ulang sebelum melanjutkan.',
    'confirmLabel' => 'Hapus',
    'cancelLabel' => 'Batal',
    'maxWidth' => 'md',
])

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-red-500/20 bg-[var(--bg-card)] p-4 sm:p-6']) }}>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-red-300/90">
                Opsi hapus
            </p>
            <h3 class="text-base font-semibold text-[var(--text-strong)]">
                {{ $title }}
            </h3>
            <p class="max-w-2xl text-sm leading-6 text-[var(--text-muted)]">
                {{ $description }}
            </p>
        </div>

        <x-danger-button
            type="button"
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', '{{ $modalName }}')"
        >
            {{ $triggerLabel }}
        </x-danger-button>
    </div>

    <x-modal name="{{ $modalName }}" :show="false" maxWidth="{{ $maxWidth }}" focusable>
        <form method="post" action="{{ $action }}" class="p-6">
            @csrf
            @method('delete')

            <div class="flex items-start gap-4">
                <div class="mt-1 flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-500/10 text-red-400">
                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true">
                        <path d="M12 8v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        <path d="M12 16h.01" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" />
                        <path d="M10.29 3.86 2.82 18a2 2 0 0 0 1.77 2.95h14.82A2 2 0 0 0 21.18 18L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                    </svg>
                </div>

                <div class="space-y-2">
                    <h2 class="text-lg font-semibold text-[var(--text-strong)]">
                        {{ $modalTitle }}
                    </h2>
                    <p class="text-sm leading-6 text-[var(--text-muted)]">
                        {{ $modalDescription }}
                    </p>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ $cancelLabel }}
                </x-secondary-button>

                <x-danger-button>
                    {{ $confirmLabel }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
