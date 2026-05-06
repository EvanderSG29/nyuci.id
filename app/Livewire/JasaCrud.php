<?php

namespace App\Livewire;

use App\Models\Jasa;
use App\Models\Toko;
use Livewire\Component;
use Livewire\WithPagination;

class JasaCrud extends Component
{
    use WithPagination;

    public $isOpen = false;

    public $isDeleteOpen = false;

    public $jasaId;

    public $toko_id;

    public $nama_jasa;

    public $satuan;

    public $harga;

    protected $rules = [
        'toko_id' => 'required|exists:tokos,id',
        'nama_jasa' => 'required|string|max:255',
        'satuan' => 'required|string|max:255',
        'harga' => 'required|integer|min:0',
    ];

    public function render()
    {
        $jasas = Jasa::where('toko_id', auth()->user()->toko->id)->with('toko')->paginate(10);
        $tokos = [auth()->user()->toko]; // Only user's toko

        return view('livewire.jasa-crud', compact('jasas', 'tokos'));
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isOpen = true;
    }

    public function store()
    {
        $this->authorize('create', Jasa::class);
        $this->validate();

        Jasa::create([
            'toko_id' => auth()->user()->toko->id,
            'nama_jasa' => $this->nama_jasa,
            'satuan' => $this->satuan,
            'harga' => $this->harga,
        ]);

        session()->flash('message', 'Jasa created successfully.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $jasa = Jasa::findOrFail($id);
        $this->jasaId = $id;
        $this->toko_id = $jasa->toko_id;
        $this->nama_jasa = $jasa->nama_jasa;
        $this->satuan = $jasa->satuan;
        $this->harga = $jasa->harga;

        $this->isOpen = true;
    }

    public function update()
    {
        $this->validate();

        if ($this->jasaId) {
            $jasa = Jasa::find($this->jasaId);
            $this->authorize('update', $jasa);
            $jasa->update([
                'toko_id' => $this->toko_id,
                'nama_jasa' => $this->nama_jasa,
                'satuan' => $this->satuan,
                'harga' => $this->harga,
            ]);

            session()->flash('message', 'Jasa updated successfully.');
            $this->closeModal();
            $this->resetInputFields();
        }
    }

    public function deleteId($id)
    {
        $this->jasaId = $id;
        $this->isDeleteOpen = true;
    }

    public function delete()
    {
        $jasa = Jasa::find($this->jasaId);
        $this->authorize('delete', $jasa);
        $jasa->delete();
        session()->flash('message', 'Jasa deleted successfully.');
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->isDeleteOpen = false;
    }

    private function resetInputFields()
    {
        $this->jasaId = null;
        $this->toko_id = null;
        $this->nama_jasa = '';
        $this->satuan = '';
        $this->harga = '';
    }
}
