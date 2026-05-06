<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Jasa Management</h1>
        <flux:button wire:click="create" variant="primary">Add Jasa</flux:button>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-2 border-b">ID</th>
                    <th class="px-4 py-2 border-b">Toko</th>
                    <th class="px-4 py-2 border-b">Nama Jasa</th>
                    <th class="px-4 py-2 border-b">Satuan</th>
                    <th class="px-4 py-2 border-b">Harga</th>
                    <th class="px-4 py-2 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($jasas as $jasa)
                    <tr>
                        <td class="px-4 py-2 border-b">{{ $jasa->id }}</td>
                        <td class="px-4 py-2 border-b">{{ $jasa->toko->nama_toko ?? 'N/A' }}</td>
                        <td class="px-4 py-2 border-b">{{ $jasa->nama_jasa }}</td>
                        <td class="px-4 py-2 border-b">{{ $jasa->satuan }}</td>
                        <td class="px-4 py-2 border-b">Rp {{ number_format($jasa->harga, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 border-b">
                            <flux:button wire:click="edit({{ $jasa->id }})" variant="outline" size="sm">Edit</flux:button>
                            <flux:button wire:click="deleteId({{ $jasa->id }})" variant="danger" size="sm" class="ml-2">Delete</flux:button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $jasas->links() }}

    <!-- Create/Edit Modal -->
    <flux:modal name="jasa-modal" wire:model="isOpen" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $jasaId ? 'Edit Jasa' : 'Create Jasa' }}</flux:heading>
                <flux:text class="mt-2">{{ $jasaId ? 'Update the jasa details.' : 'Add a new jasa.' }}</flux:text>
            </div>

            <flux:select label="Toko" wire:model="toko_id" placeholder="Select Toko">
                @foreach ($tokos as $toko)
                    <flux:option value="{{ $toko->id }}">{{ $toko->nama_toko }}</flux:option>
                @endforeach
            </flux:select>

            <flux:input label="Nama Jasa" wire:model="nama_jasa" placeholder="Nama Jasa" />

            <flux:input label="Satuan" wire:model="satuan" placeholder="Satuan" />

            <flux:input label="Harga" type="number" wire:model="harga" placeholder="Harga" />

            <div class="flex">
                <flux:spacer />
                <flux:button wire:click="closeModal" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="{{ $jasaId ? 'update' : 'store' }}" variant="primary" class="ml-2">
                    {{ $jasaId ? 'Update' : 'Create' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Delete Confirmation Modal -->
    <flux:modal name="delete-jasa-modal" wire:model="isDeleteOpen" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Jasa</flux:heading>
                <flux:text class="mt-2">Are you sure you want to delete this jasa? This action cannot be undone.</flux:text>
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button wire:click="closeModal" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="delete" variant="danger" class="ml-2">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</div>