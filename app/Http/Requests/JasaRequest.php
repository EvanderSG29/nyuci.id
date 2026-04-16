<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JasaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'nama_jasa' => ['required', 'string', 'max:255'],
            'satuan' => ['required', 'string', 'max:50'],
            'harga' => ['required', 'integer', 'min:0'],
        ];
    }
}
