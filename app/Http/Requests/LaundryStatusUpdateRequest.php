<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;

class LaundryStatusUpdateRequest extends FormRequest
{
    private const STATUSES = ['belum_selesai', 'proses', 'selesai'];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed|RequiredIf>>
     */
    public function rules(): array
    {
        $laundry = $this->route('laundry');
        $startedAt = $laundry?->tanggal_dimulai?->format('Y-m-d');

        return [
            'status_laundry_id' => ['required', 'integer', Rule::exists('laundries', 'id')],
            'status' => ['required', Rule::in(self::STATUSES)],
            'tgl_selesai' => array_filter([
                'nullable',
                'date',
                Rule::requiredIf($this->input('status') === 'selesai'),
                $startedAt ? 'after_or_equal:'.$startedAt : null,
            ]),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'tgl_selesai' => blank($this->input('tgl_selesai')) ? null : $this->input('tgl_selesai'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status_laundry_id.required' => 'Data laundry wajib dipilih.',
            'status_laundry_id.exists' => 'Data laundry yang dipilih tidak valid.',
            'status.required' => 'Status laundry wajib dipilih.',
            'status.in' => 'Status laundry tidak valid.',
            'tgl_selesai.required' => 'Tanggal selesai wajib diisi saat status laundry selesai.',
            'tgl_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal masuk.',
        ];
    }
}
