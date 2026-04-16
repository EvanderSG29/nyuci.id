<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;

class LaundryStatusUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string|RequiredIf>>
     */
    public function rules(): array
    {
        return [
            'status_laundry_id' => ['required', 'integer'],
            'status' => ['required', Rule::in(['belum_selesai', 'proses', 'selesai'])],
            'tgl_selesai' => ['nullable', 'date', Rule::requiredIf($this->input('status') === 'selesai')],
        ];
    }
}
