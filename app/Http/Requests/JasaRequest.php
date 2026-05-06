<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JasaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $tokoId = $this->user()?->toko?->id;
        $jasaId = $this->route('jasa')?->id;

        return [
            'nama_jasa' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('jasas', 'nama_jasa')
                    ->where(fn ($query) => $query
                        ->where('toko_id', $tokoId)
                        ->where('satuan', $this->input('satuan')))
                    ->ignore($jasaId),
            ],
            'satuan' => ['required', 'string', 'min:1', 'max:50', 'regex:/^[\pL\pN\s.\/-]+$/u'],
            'harga' => ['required', 'integer', 'min:1', 'max:999999999999'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama_jasa' => $this->normalizeNullableString($this->input('nama_jasa')),
            'satuan' => str($this->normalizeNullableString($this->input('satuan')) ?? '')->lower()->toString() ?: null,
            'harga' => $this->normalizePrice($this->input('harga')),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_jasa.required' => 'Nama jasa wajib diisi.',
            'nama_jasa.min' => 'Nama jasa minimal :min karakter.',
            'nama_jasa.unique' => 'Kombinasi nama jasa dan satuan sudah ada.',
            'satuan.required' => 'Satuan wajib diisi.',
            'satuan.regex' => 'Satuan hanya boleh berisi huruf, angka, spasi, titik, garis miring, atau tanda hubung.',
            'harga.required' => 'Harga wajib diisi.',
            'harga.integer' => 'Harga harus berupa angka bulat.',
            'harga.min' => 'Harga harus lebih besar dari 0.',
        ];
    }

    private function normalizePrice(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $price = trim($value);

        if (! preg_match('/^[0-9\s.,]+$/', $price)) {
            return $price;
        }

        return str_replace(['.', ',', ' '], '', $price);
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
