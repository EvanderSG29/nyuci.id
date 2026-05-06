<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIdentityUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nama_toko' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'no_hp' => ['nullable', 'string', 'max:30'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama_toko' => trim((string) $this->input('nama_toko')),
            'alamat' => blank($this->input('alamat'))
                ? null
                : trim((string) $this->input('alamat')),
            'no_hp' => blank($this->input('no_hp'))
                ? null
                : trim((string) $this->input('no_hp')),
        ]);
    }
}
