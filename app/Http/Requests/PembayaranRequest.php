<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PembayaranRequest extends FormRequest
{
    private const METHODS = ['cash', 'qris', 'transfer', 'ewallet'];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'laundry_id' => ['required', 'integer', 'exists:laundries,id'],
            'metode_pembayaran' => ['required', 'string', 'max:50', Rule::in(self::METHODS)],
            'tgl_pembayaran' => ['required', 'date'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['belum_bayar', 'sudah_bayar'])],
        ];
    }
}
