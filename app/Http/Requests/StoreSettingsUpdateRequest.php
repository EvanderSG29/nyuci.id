<?php

namespace App\Http\Requests;

use App\Models\Toko;
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
        $rules = [
            'nama_toko' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'dashboard_cards' => ['nullable', 'array'],
        ];

        foreach (array_keys(Toko::dashboardCardOptions()) as $key) {
            $rules["dashboard_cards.$key"] = ['nullable', 'boolean'];
        }

        return $rules;
    }
}
