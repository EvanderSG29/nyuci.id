<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LaundryRequest extends FormRequest
{
    private const STATUSES = ['belum_selesai', 'proses', 'selesai'];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $tokoId = $this->user()?->toko?->id;

        return [
            'klien_id' => [
                'required',
                'integer',
                Rule::exists('kliens', 'id')->where(fn ($query) => $query->where('toko_id', $tokoId)),
            ],
            'jasa_id' => [
                'required',
                'integer',
                Rule::exists('jasas', 'id')->where(fn ($query) => $query->where('toko_id', $tokoId)),
            ],
            'qty' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/', 'gt:0', 'max:99999999.99'],
            'status' => ['required', Rule::in(self::STATUSES)],
            'tanggal_dimulai' => ['required', 'date'],
            'ets_selesai' => ['required', 'date', 'after_or_equal:tanggal_dimulai'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'klien_id' => $this->input('klien_id'),
            'jasa_id' => $this->input('jasa_id'),
            'qty' => is_string($this->input('qty'))
                ? str_replace(',', '.', trim($this->input('qty')))
                : $this->input('qty'),
            'status' => $this->input('status'),
            'tanggal_dimulai' => blank($this->input('tanggal_dimulai')) ? null : $this->input('tanggal_dimulai'),
            'ets_selesai' => blank($this->input('ets_selesai')) ? null : $this->input('ets_selesai'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'klien_id.required' => 'Pelanggan wajib dipilih.',
            'klien_id.exists' => 'Pelanggan tidak valid untuk toko ini.',
            'jasa_id.required' => 'Biaya jasa wajib dipilih.',
            'jasa_id.exists' => 'Biaya jasa tidak valid untuk toko ini.',
            'qty.required' => 'Jumlah laundry wajib diisi.',
            'qty.numeric' => 'Jumlah laundry harus berupa angka.',
            'qty.regex' => 'Jumlah laundry maksimal 2 angka di belakang koma.',
            'qty.gt' => 'Jumlah laundry harus lebih besar dari 0.',
            'status.required' => 'Status laundry wajib dipilih.',
            'status.in' => 'Status laundry tidak valid.',
            'tanggal_dimulai.required' => 'Tanggal masuk wajib diisi.',
            'ets_selesai.required' => 'Estimasi selesai wajib diisi.',
            'ets_selesai.after_or_equal' => 'Estimasi selesai tidak boleh lebih awal dari tanggal masuk.',
        ];
    }
}
