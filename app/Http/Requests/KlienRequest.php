<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KlienRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $tokoId = $this->user()?->toko?->id;
        $klienId = $this->route('klien')?->id;

        return [
            'nama_klien' => ['required', 'string', 'min:2', 'max:255'],
            'email_klien' => ['nullable', 'email:rfc', 'max:255'],
            'alamat_klien' => ['nullable', 'string', 'max:255'],
            'no_hp_klien' => [
                'required',
                'string',
                'min:8',
                'max:30',
                'regex:/^\+?[0-9][0-9\s().-]{7,29}$/',
                Rule::unique('kliens', 'no_hp_klien')
                    ->where(fn ($query) => $query->where('toko_id', $tokoId))
                    ->ignore($klienId),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama_klien' => $this->normalizeNullableString($this->input('nama_klien')),
            'email_klien' => str($this->normalizeNullableString($this->input('email_klien')) ?? '')->lower()->toString() ?: null,
            'alamat_klien' => $this->normalizeNullableString($this->input('alamat_klien')),
            'no_hp_klien' => $this->normalizePhoneNumber($this->input('no_hp_klien')),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_klien.required' => 'Nama pelanggan wajib diisi.',
            'nama_klien.min' => 'Nama pelanggan minimal :min karakter.',
            'email_klien.email' => 'Email pelanggan harus berupa alamat email yang valid.',
            'no_hp_klien.required' => 'Nomor HP pelanggan wajib diisi.',
            'no_hp_klien.regex' => 'Nomor HP pelanggan hanya boleh berisi angka, spasi, tanda +, titik, tanda kurung, atau tanda hubung.',
            'no_hp_klien.unique' => 'Nomor HP pelanggan sudah terdaftar.',
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

    private function normalizePhoneNumber(mixed $value): ?string
    {
        $phone = $this->normalizeNullableString($value);

        if ($phone === null) {
            return null;
        }

        return preg_replace('/[\s().-]+/', '', $phone) ?: null;
    }
}
