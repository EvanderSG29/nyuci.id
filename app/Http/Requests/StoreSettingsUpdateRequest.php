<?php

namespace App\Http\Requests;

use App\Models\Toko;
use Illuminate\Foundation\Http\FormRequest;

class StoreSettingsUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $rules = [
            'nama_toko' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'payment_gateway_qris_payload' => ['nullable', 'string', 'max:10000'],
            'payment_gateway_qris_merchant_name' => ['nullable', 'string', 'max:255'],
            'payment_gateway_checkout_ttl_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'dashboard_cards' => ['nullable', 'array'],
        ];

        foreach (array_keys(Toko::dashboardCardOptions()) as $key) {
            $rules["dashboard_cards.$key"] = ['nullable', 'boolean'];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'payment_gateway_qris_payload' => blank($this->input('payment_gateway_qris_payload'))
                ? null
                : trim((string) $this->input('payment_gateway_qris_payload')),
            'payment_gateway_qris_merchant_name' => blank($this->input('payment_gateway_qris_merchant_name'))
                ? null
                : trim((string) $this->input('payment_gateway_qris_merchant_name')),
            'payment_gateway_checkout_ttl_minutes' => blank($this->input('payment_gateway_checkout_ttl_minutes'))
                ? null
                : $this->input('payment_gateway_checkout_ttl_minutes'),
        ]);
    }
}
