@php
    $notifications = $headerNotifications ?? collect();
    $unreadCount = $headerUnreadNotificationCount ?? 0;
@endphp

<flux:dropdown position="bottom" align="end">
    <flux:button variant="subtle" square aria-label="Notifikasi" class="relative">
        <flux:icon.bell variant="outline" class="size-5" />

        @if ($unreadCount > 0)
            <span class="absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-[var(--primary)] px-1.5 text-[0.65rem] font-semibold text-white ring-2 ring-[var(--bg-card)]">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </flux:button>

    <flux:menu class="w-[24rem] max-w-[calc(100vw-2rem)] !border-[var(--border-main)] !bg-[var(--bg-card)]">
        <div class="px-2 py-1">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-[var(--text-strong)]">Notifikasi</p>
                    <p class="mt-1 text-xs text-[var(--text-muted)]">Update order terbaru yang belum Anda baca.</p>
                </div>

                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf

                        <button type="submit" class="rounded-full border border-[var(--border-main)] px-3 py-1 text-[0.7rem] font-semibold text-[var(--text-muted)] transition hover:border-[var(--primary)] hover:text-[var(--text-strong)]">
                            Tandai semua
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <flux:separator class="my-1" />

        @forelse ($notifications as $notification)
            @php
                $data = $notification->data;
                $actionUrl = $data['action_url'] ?? route('laundry.index');
            @endphp

            <div class="px-2 py-1">
                <div class="overflow-hidden rounded-2xl border border-[var(--border-main)] {{ $notification->read_at === null ? 'bg-[var(--bg-surface)]' : 'bg-transparent opacity-75' }}">
                    <a href="{{ $actionUrl }}" wire:navigate class="block px-3 py-3 transition hover:bg-[var(--bg-surface)]/70">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 rounded-2xl bg-[var(--primary)]/12 p-2 text-[var(--primary)]">
                                @if (($data['icon'] ?? null) === 'truck')
                                    <flux:icon.truck class="size-4" />
                                @else
                                    <flux:icon.bell class="size-4" />
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="truncate text-sm font-semibold text-[var(--text-strong)]">
                                        {{ $data['title'] ?? 'Notifikasi baru' }}
                                    </p>

                                    @if ($notification->read_at === null)
                                        <span class="size-2 rounded-full bg-[var(--primary)]"></span>
                                    @endif
                                </div>

                                <p class="mt-1 text-xs leading-5 text-[var(--text-muted)]">
                                    {{ $data['message'] ?? 'Ada pembaruan baru untuk Anda.' }}
                                </p>

                                <p class="mt-2 text-[0.7rem] font-medium uppercase tracking-[0.18em] text-[var(--text-muted)]">
                                    {{ $notification->created_at?->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </a>

                    <div class="flex items-center justify-between gap-2 border-t border-[var(--border-main)] px-3 py-2">
                        <span class="truncate text-[0.75rem] text-[var(--text-muted)]">
                            {{ $data['action_label'] ?? 'Buka detail' }}
                        </span>

                        @if ($notification->read_at === null)
                            <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                @csrf
                                @method('PATCH')

                                <button type="submit" class="text-[0.75rem] font-semibold text-[var(--primary)] transition hover:text-[var(--primary-hover)]">
                                    Tandai dibaca
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="px-4 py-6 text-center">
                <p class="text-sm font-semibold text-[var(--text-strong)]">Belum ada notifikasi baru.</p>
                <p class="mt-1 text-xs text-[var(--text-muted)]">Notifikasi database akan muncul di sini setelah queue worker memproses job.</p>
            </div>
        @endforelse
    </flux:menu>
</flux:dropdown>
