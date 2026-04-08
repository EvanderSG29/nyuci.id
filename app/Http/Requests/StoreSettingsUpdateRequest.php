<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingsUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'nama_toko' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'background_mode' => ['required', 'in:system,custom'],
            'background_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/', 'required_if:background_mode,custom'],
        ];
    }
}
