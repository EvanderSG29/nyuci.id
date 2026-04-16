<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class LaundryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'klien_id' => ['required', 'integer', 'exists:kliens,id'],
            'jasa_id' => ['required', 'integer', 'exists:jasas,id'],
            'qty' => ['required', 'numeric', 'gt:0'],
            'status' => ['required', Rule::in(['belum_selesai', 'proses', 'selesai'])],
            'tanggal_dimulai' => ['required', 'date'],
            'ets_selesai' => ['required', 'date', 'after_or_equal:tanggal_dimulai'],
        ];
    }
}
