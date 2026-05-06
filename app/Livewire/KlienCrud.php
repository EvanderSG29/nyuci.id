<?php

namespace App\Livewire;

use App\Models\Klien;
use Livewire\Component;
use Livewire\WithPagination;

class KlienCrud extends Component
{
    use WithPagination;

    public $isOpen = false;

    public $isDeleteOpen = false;

    public $klienId;

    public $nama_klien;

    public $email_klien;

    public $alamat_klien;

    public $no_hp_klien;

    protected $rules = [
        'nama_klien' => 'required|string|max:255',
        'email_klien' => 'required|email|unique:kliens,email_klien',
        'alamat_klien' => 'required|string',
        'no_hp_klien' => 'required|string|max:20',
    ];

    protected $messages = [
        'email_klien.unique' => 'Email sudah digunakan.',
    ];

    public function render()
    {
        $kliens = Klien::where('toko_id', auth()->user()->toko->id)->paginate(10);

        return view('livewire.klien-crud', compact('kliens'));
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isOpen = true;
    }

    public function store()
    {
        $this->authorize('create', Klien::class);
        $this->validate();

        Klien::create([
            'toko_id' => auth()->user()->toko->id,
            'nama_klien' => $this->nama_klien,
            'email_klien' => $this->email_klien,
            'alamat_klien' => $this->alamat_klien,
            'no_hp_klien' => $this->no_hp_klien,
        ]);

        session()->flash('message', 'Klien created successfully.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $klien = Klien::findOrFail($id);
        $this->authorize('update', $klien);
        $this->klienId = $id;
        $this->nama_klien = $klien->nama_klien;
        $this->email_klien = $klien->email_klien;
        $this->alamat_klien = $klien->alamat_klien;
        $this->no_hp_klien = $klien->no_hp_klien;

        $this->isOpen = true;
    }

    public function update()
    {
        $this->validate([
            'nama_klien' => 'required|string|max:255',
            'email_klien' => 'required|email|unique:kliens,email_klien,'.$this->klienId,
            'alamat_klien' => 'required|string',
            'no_hp_klien' => 'required|string|max:20',
        ]);

        if ($this->klienId) {
            $klien = Klien::find($this->klienId);
            $this->authorize('update', $klien);
            $klien->update([
                'nama_klien' => $this->nama_klien,
                'email_klien' => $this->email_klien,
                'alamat_klien' => $this->alamat_klien,
                'no_hp_klien' => $this->no_hp_klien,
            ]);

            session()->flash('message', 'Klien updated successfully.');
            $this->closeModal();
            $this->resetInputFields();
        }
    }

    public function deleteId($id)
    {
        $this->klienId = $id;
        $this->isDeleteOpen = true;
    }

    public function delete()
    {
        $klien = Klien::find($this->klienId);
        $this->authorize('delete', $klien);
        $klien->delete();
        session()->flash('message', 'Klien deleted successfully.');
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->isDeleteOpen = false;
    }

    private function resetInputFields()
    {
        $this->klienId = null;
        $this->nama_klien = '';
        $this->email_klien = '';
        $this->alamat_klien = '';
        $this->no_hp_klien = '';
    }
}
