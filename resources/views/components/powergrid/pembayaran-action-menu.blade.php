@props([
    'rowId',
    'viewUrl' => null,
    'editUrl' => null,
    'checkoutUrl' => null,
    'markPaidUrl' => null,
    'deleteUrl' => null,
    'modalComponent' => null,
    'canView' => true,
    'canUpdate' => false,
    'canDelete' => false,
    'canMarkPaid' => false,
])

<x-dropdown align="right" width="56" contentClasses="py-2 bg-[var(--bg-card)]">
    <x-slot name="trigger">
        <button
            type="button"
            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-[var(--border-main)] bg-[var(--bg-card)] text-[var(--text-muted)] transition hover:bg-[var(--bg-surface)] hover:text-[var(--text-strong)]"
        >
            <span class="sr-only">Open actions for pembayaran #{{ $rowId }}</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M10 4.75a1.25 1.25 0 1 1 0-2.5 1.25 1.25 0 0 1 0 2.5Zm0 6.5a1.25 1.25 0 1 1 0-2.5 1.25 1.25 0 0 1 0 2.5Zm0 6.5a1.25 1.25 0 1 1 0-2.5 1.25 1.25 0 0 1 0 2.5Z" />
            </svg>
        </button>
    </x-slot>

    <x-slot name="content">
        @if ($canView && filled($viewUrl))
            <x-dropdown-link :href="$viewUrl" wire:navigate>
                Detail
            </x-dropdown-link>
        @endif

        @if ($canUpdate && filled($modalComponent))
            <button
                type="button"
                wire:click="$dispatch('openModal', { component: '{{ $modalComponent }}', arguments: { pembayaran: {{ (int) $rowId }} } })"
                class="block w-full px-4 py-2 text-start text-sm leading-5 text-[var(--text-main)] transition duration-150 ease-in-out hover:bg-[var(--bg-surface)] focus:bg-[var(--bg-surface)] focus:outline-none"
            >
                Edit (Modal)
            </button>
        @elseif ($canUpdate && filled($editUrl))
            <x-dropdown-link :href="$editUrl" wire:navigate>
                Edit
            </x-dropdown-link>
        @endif

        @if (filled($checkoutUrl))
            <x-dropdown-link :href="$checkoutUrl" target="_blank" rel="noopener noreferrer">
                Buka Checkout
            </x-dropdown-link>
        @endif

        @if ($canMarkPaid && filled($markPaidUrl))
            <x-dropdown-link :href="$markPaidUrl">
                Tandai Lunas
            </x-dropdown-link>
        @endif

        @if ($canDelete && filled($deleteUrl))
            <div class="my-1 border-t border-[var(--border-main)]"></div>

            <form method="POST" action="{{ $deleteUrl }}" onsubmit="return confirm('Hapus pembayaran ini?');">
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="block w-full px-4 py-2 text-start text-sm font-medium text-red-600 transition hover:bg-red-50/80 focus:bg-red-50/80 focus:outline-none"
                >
                    Hapus
                </button>
            </form>
        @endif
    </x-slot>
</x-dropdown>
