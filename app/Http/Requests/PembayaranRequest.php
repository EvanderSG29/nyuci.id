<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PembayaranRequest extends FormRequest
{
    private const METHODS = ['cash', 'qris', 'transfer', 'ewallet'];

    private const STATUSES = ['belum_bayar', 'sudah_bayar'];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $tokoId = $this->user()?->toko?->id;
        $pembayaran = $this->route('pembayaran');
        $pembayaranId = $pembayaran?->id;

        return [
            'laundry_id' => [
                'required',
                'integer',
                Rule::exists('laundries', 'id')->where(fn ($query) => $query->where('toko_id', $tokoId)),
                Rule::unique('pembayarans', 'laundry_id')->ignore($pembayaranId),
            ],
            'metode_pembayaran' => ['required', 'string', 'max:50', Rule::in(self::METHODS)],
            'tgl_pembayaran' => [
                Rule::requiredIf($this->input('status') === 'sudah_bayar'),
                'nullable',
                'date',
                'before_or_equal:today',
                'prohibited_unless:status,sudah_bayar',
            ],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(self::STATUSES)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'tgl_pembayaran' => blank($this->input('tgl_pembayaran'))
                ? null
                : $this->input('tgl_pembayaran'),
            'catatan' => $this->normalizeNullableString($this->input('catatan')),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'laundry_id.required' => 'Laundry wajib dipilih.',
            'laundry_id.exists' => 'Laundry yang dipilih tidak valid untuk toko ini.',
            'laundry_id.unique' => 'Laundry ini sudah memiliki data pembayaran.',
            'metode_pembayaran.required' => 'Metode pembayaran wajib dipilih.',
            'metode_pembayaran.in' => 'Metode pembayaran tidak valid.',
            'tgl_pembayaran.required' => 'Tanggal pembayaran wajib diisi saat status sudah bayar.',
            'tgl_pembayaran.before_or_equal' => 'Tanggal pembayaran tidak boleh melebihi hari ini.',
            'tgl_pembayaran.prohibited_unless' => 'Tanggal pembayaran hanya boleh diisi saat status sudah bayar.',
            'catatan.max' => 'Catatan maksimal :max karakter.',
            'status.required' => 'Status pembayaran wajib dipilih.',
            'status.in' => 'Status pembayaran tidak valid.',
        ];
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim(preg_replace('/\s+/', ' ', (string) $value) ?? '');

        return $normalized === '' ? null : $normalized;
    }
}
