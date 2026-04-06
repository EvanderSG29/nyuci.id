<x-app-layout title="Laundry">
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-[var(--text-strong)]">Daftar Laundry</h2>
            <a href="{{ route('laundry.create') }}" class="inline-flex items-center rounded-full bg-[var(--primary)] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[var(--primary-hover)]">
                Tambah Laundry
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if ($data->isEmpty())
                <div class="rounded-3xl border border-[var(--border-main)] bg-[var(--bg-card)] p-6">
                    <p class="text-[var(--text-main)]">
                        Belum ada data laundry. Mulai dengan
                        <a href="{{ route('laundry.create') }}" class="font-semibold text-[var(--primary-soft)] underline hover:text-white">menambah data baru</a>.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3 mb-6">
                    <div class="rounded-2xl border border-[var(--border-main)] bg-[var(--bg-card)] p-4">
                        <p class="text-sm font-semibold text-[var(--text-muted)]">Total Laundry</p>
                        <p class="mt-2 text-2xl font-bold text-[var(--text-strong)]">{{ $data->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#3b82f6]/35 bg-[#0f1a2e] p-4">
                        <p class="text-sm font-semibold text-[#bfdbfe]">Belum Diambil</p>
                        <p class="mt-2 text-2xl font-bold text-[#dbeafe]">{{ $data->where('is_taken', false)->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#60a5fa]/45 bg-[#13203a] p-4">
                        <p class="text-sm font-semibold text-[#bfdbfe]">Sudah Diambil</p>
                        <p class="mt-2 text-2xl font-bold text-white">{{ $data->where('is_taken', true)->count() }}</p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-3xl border border-[var(--border-main)] bg-[var(--bg-card)] shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-[var(--text-main)]">
                            <thead class="border-b border-[var(--border-main)] bg-[var(--bg-surface)]">
                                <tr class="text-left text-xs uppercase tracking-[0.18em] text-[var(--text-muted)]">
                                    <th class="px-4 py-3 font-semibold">No</th>
                                    <th class="px-4 py-3 font-semibold">Nama Customer</th>
                                    <th class="px-4 py-3 font-semibold">No HP</th>
                                    <th class="px-4 py-3 font-semibold">Berat (kg)</th>
                                    <th class="px-4 py-3 font-semibold">Layanan</th>
                                    <th class="px-4 py-3 font-semibold">Tgl Masuk</th>
                                    <th class="px-4 py-3 font-semibold">Est. Selesai</th>
                                    <th class="px-4 py-3 font-semibold">Status</th>
                                    <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $key => $d)
                                    <tr class="border-b border-[#1e293b]/70 transition hover:bg-[var(--bg-surface)]">
                                        <td class="px-4 py-3">{{ $key + 1 }}</td>
                                        <td class="px-4 py-3 font-semibold text-[var(--text-strong)]">{{ $d->nama }}</td>
                                        <td class="px-4 py-3">{{ $d->no_hp }}</td>
                                        <td class="px-4 py-3">{{ $d->berat }}</td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full bg-[#1e293b] px-3 py-1 text-xs font-semibold text-[#cbd5e1]">
                                                {{ ucfirst($d->layanan) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">{{ \Carbon\Carbon::parse($d->tanggal)->format('d M Y') }}</td>
                                        <td class="px-4 py-3">{{ \Carbon\Carbon::parse($d->estimasi_selesai)->format('d M Y') }}</td>
                                        <td class="px-4 py-3">
                                            @if ($d->is_taken)
                                                <span class="rounded-full bg-[#2563eb] px-3 py-1 text-xs font-semibold text-white">
                                                    Diambil
                                                </span>
                                            @else
                                                <span class="rounded-full bg-[#0f1a2e] px-3 py-1 text-xs font-semibold text-[#bfdbfe]">
                                                    Belum diambil
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-center gap-2">
                                                <a href="{{ route('laundry.toggle', $d->id) }}" class="rounded-lg border border-[var(--border-soft)] bg-[var(--bg-surface)] px-2 py-1 text-xs font-medium text-[var(--text-main)] transition hover:border-[var(--primary)]" title="Toggle Status">
                                                    Toggle
                                                </a>
                                                <a href="{{ route('laundry.edit', $d->id) }}" class="rounded-lg border border-[#3b82f6]/60 bg-[#1e3a8a] px-2 py-1 text-xs font-medium text-white transition hover:bg-[var(--primary)]" title="Edit">
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('laundry.destroy', $d->id) }}" class="inline" onsubmit="return confirm('Yakin hapus?')">
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
