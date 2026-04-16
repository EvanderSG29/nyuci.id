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
            'tgl_pembayaran' => [Rule::requiredIf($this->input('status') === 'sudah_bayar'), 'nullable', 'date'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['belum_bayar', 'sudah_bayar'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'tgl_pembayaran' => blank($this->input('tgl_pembayaran'))
                ? null
                : $this->input('tgl_pembayaran'),
        ]);
    }
}
