<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Klien Management</h1>
        <flux:button wire:click="create" variant="primary">Add Klien</flux:button>
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
                    <th class="px-4 py-2 border-b">Nama Klien</th>
                    <th class="px-4 py-2 border-b">Email</th>
                    <th class="px-4 py-2 border-b">Alamat</th>
                    <th class="px-4 py-2 border-b">No HP</th>
                    <th class="px-4 py-2 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kliens as $klien)
                    <tr>
                        <td class="px-4 py-2 border-b">{{ $klien->id }}</td>
                        <td class="px-4 py-2 border-b">{{ $klien->nama_klien }}</td>
                        <td class="px-4 py-2 border-b">{{ $klien->email_klien }}</td>
                        <td class="px-4 py-2 border-b">{{ $klien->alamat_klien }}</td>
                        <td class="px-4 py-2 border-b">{{ $klien->no_hp_klien }}</td>
                        <td class="px-4 py-2 border-b">
                            <flux:button wire:click="edit({{ $klien->id }})" variant="outline" size="sm">Edit</flux:button>
                            <flux:button wire:click="deleteId({{ $klien->id }})" variant="danger" size="sm" class="ml-2">Delete</flux:button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $kliens->links() }}

    <!-- Create/Edit Modal -->
    <flux:modal name="klien-modal" wire:model="isOpen" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $klienId ? 'Edit Klien' : 'Create Klien' }}</flux:heading>
                <flux:text class="mt-2">{{ $klienId ? 'Update the klien details.' : 'Add a new klien.' }}</flux:text>
            </div>

            <flux:input label="Nama Klien" wire:model="nama_klien" placeholder="Nama Klien" />

            <flux:input label="Email Klien" type="email" wire:model="email_klien" placeholder="Email Klien" />

            <flux:textarea label="Alamat Klien" wire:model="alamat_klien" placeholder="Alamat Klien" />

            <flux:input label="No HP Klien" wire:model="no_hp_klien" placeholder="No HP Klien" />

            <div class="flex">
                <flux:spacer />
                <flux:button wire:click="closeModal" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="{{ $klienId ? 'update' : 'store' }}" variant="primary" class="ml-2">
                    {{ $klienId ? 'Update' : 'Create' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Delete Confirmation Modal -->
    <flux:modal name="delete-klien-modal" wire:model="isDeleteOpen" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Klien</flux:heading>
                <flux:text class="mt-2">Are you sure you want to delete this klien? This action cannot be undone.</flux:text>
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button wire:click="closeModal" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="delete" variant="danger" class="ml-2">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</div>