<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentQrisSettingsUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'payment_gateway_qris_payload' => ['nullable', 'string', 'max:10000'],
            'payment_gateway_qris_merchant_name' => ['nullable', 'string', 'max:255'],
            'payment_gateway_checkout_ttl_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
        ];
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
