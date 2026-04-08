<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KlienRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'nama_klien' => ['required', 'string', 'max:255'],
            'alamat_klien' => ['nullable', 'string', 'max:255'],
            'no_hp_klien' => ['required', 'string', 'max:30'],
        ];
    }
}
