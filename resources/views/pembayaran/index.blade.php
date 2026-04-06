<x-app-layout title="Pembayaran">
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-[var(--text-strong)]">Daftar Pembayaran</h2>
            <a href="{{ route('pembayaran.create') }}" class="inline-flex items-center rounded-full bg-[var(--primary)] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[var(--primary-hover)]">
                Tambah Pembayaran
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if ($data->isEmpty())
                <div class="rounded-3xl border border-[var(--border-main)] bg-[var(--bg-card)] p-6">
                    <p class="text-[var(--text-main)]">
                        Belum ada data pembayaran. Mulai dengan
                        <a href="{{ route('pembayaran.create') }}" class="font-semibold text-[var(--primary-soft)] underline hover:text-white">menambah pembayaran baru</a>.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3 mb-6">
                    <div class="rounded-2xl border border-[var(--border-main)] bg-[var(--bg-card)] p-4">
                        <p class="text-sm font-semibold text-[var(--text-muted)]">Total Pembayaran</p>
                        <p class="mt-2 text-2xl font-bold text-[var(--text-strong)]">{{ $data->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#60a5fa]/45 bg-[#13203a] p-4">
                        <p class="text-sm font-semibold text-[#bfdbfe]">Sudah Bayar</p>
                        <p class="mt-2 text-2xl font-bold text-white">{{ $data->where('status', 'sudah_bayar')->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#3b82f6]/35 bg-[#0f1a2e] p-4">
                        <p class="text-sm font-semibold text-[#bfdbfe]">Belum Bayar</p>
                        <p class="mt-2 text-2xl font-bold text-white">{{ $data->where('status', 'belum_bayar')->count() }}</p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-3xl border border-[var(--border-main)] bg-[var(--bg-card)] shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-[var(--text-main)]">
                            <thead class="border-b border-[var(--border-main)] bg-[var(--bg-surface)]">
                                <tr class="text-left text-xs uppercase tracking-[0.18em] text-[var(--text-muted)]">
                                    <th class="px-4 py-3 font-semibold">No</th>
                                    <th class="px-4 py-3 font-semibold">Nama Pelanggan</th>
                                    <th class="px-4 py-3 font-semibold">No HP</th>
                                    <th class="px-4 py-3 font-semibold">Total</th>
                                    <th class="px-4 py-3 font-semibold">Status</th>
                                    <th class="px-4 py-3 font-semibold">Tgl Input</th>
                                    <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $key => $d)
                                    <tr class="border-b border-[#1e293b]/70 transition hover:bg-[var(--bg-surface)]">
                                        <td class="px-4 py-3">{{ $key + 1 }}</td>
                                        <td class="px-4 py-3 font-semibold text-[var(--text-strong)]">{{ $d->laundry->nama ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $d->laundry->no_hp ?? '-' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full bg-[#1e293b] px-3 py-1 text-xs font-semibold text-[#dbeafe]">
                                                Rp {{ number_format($d->total, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($d->status === 'sudah_bayar')
                                                <span class="rounded-full bg-[#2563eb] px-3 py-1 text-xs font-semibold text-white">
                                                    Sudah Bayar
                                                </span>
                                            @else
                                                <span class="rounded-full bg-[#0f1a2e] px-3 py-1 text-xs font-semibold text-[#bfdbfe]">
                                                    Belum Bayar
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">{{ $d->created_at->format('d M Y H:i') }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-center gap-2">
                                                @if ($d->status === 'belum_bayar')
                                                    <a href="{{ route('pembayaran.paid', $d->id) }}" class="rounded-lg border border-[#3b82f6]/60 bg-[#1e3a8a] px-2 py-1 text-xs font-medium text-white transition hover:bg-[var(--primary)]" title="Tandai Bayar">
                                                        Bayar
                                                    </a>
                                                @endif
                                                <a href="{{ route('pembayaran.edit', $d->id) }}" class="rounded-lg border border-[var(--border-soft)] bg-[var(--bg-surface)] px-2 py-1 text-xs font-medium text-[var(--text-main)] transition hover:border-[var(--primary)]" title="Edit">
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('pembayaran.destroy', $d->id) }}" class="inline" onsubmit="return confirm('Yakin hapus?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg border border-red-500/50 bg-red-500/15 px-2 py-1 text-xs font-medium text-red-200 transition hover:bg-red-500/30" title="Hapus">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
