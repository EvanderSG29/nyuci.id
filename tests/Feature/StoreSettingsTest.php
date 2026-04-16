<?php

use App\Models\Toko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('store settings save qris configuration and other stores can use server fallback', function () {
    config()->set('payment_gateway.qris_static.payload', 'SERVER-QRIS-PAYLOAD');
    config()->set('payment_gateway.qris_static.merchant_name', 'Server Merchant');
    config()->set('payment_gateway.checkout_ttl_minutes', 30);

    $user = User::factory()->create();

    $store = Toko::create([
        'user_id' => $user->id,
        'nama_toko' => 'Laundry Alpha',
        'alamat' => 'Jl. Alpha',
        'no_hp' => '081233344455',
    ]);

    $this
        ->actingAs($user)
        ->patch(route('pengaturan-toko.update'), [
            'nama_toko' => 'Laundry Alpha',
            'alamat' => 'Jl. Alpha',
            'no_hp' => '081233344455',
            'payment_gateway_qris_payload' => 'TOKO-QRIS-PAYLOAD',
            'payment_gateway_qris_merchant_name' => 'Merchant Alpha',
            'payment_gateway_checkout_ttl_minutes' => 45,
        ])
        ->assertRedirect(route('pengaturan-toko.edit'))
        ->assertSessionHas('status', 'store-settings-updated');

    $store->refresh();

    expect($store->payment_gateway_qris_payload)->toBe('TOKO-QRIS-PAYLOAD');
    expect($store->payment_gateway_qris_merchant_name)->toBe('Merchant Alpha');
    expect($store->payment_gateway_checkout_ttl_minutes)->toBe(45);
    expect($store->resolvedPaymentGatewayQrisPayload())->toBe('TOKO-QRIS-PAYLOAD');
    expect($store->resolvedPaymentGatewayQrisMerchantName())->toBe('Merchant Alpha');
    expect($store->resolvedPaymentGatewayCheckoutTtlMinutes())->toBe(45);
    expect($store->paymentGatewayQrisConfigSource())->toBe('toko');

    $fallbackStore = Toko::create([
        'user_id' => User::factory()->create()->id,
        'nama_toko' => 'Laundry Beta',
        'alamat' => 'Jl. Beta',
        'no_hp' => '081266677788',
    ]);

    expect($fallbackStore->resolvedPaymentGatewayQrisPayload())->toBe('SERVER-QRIS-PAYLOAD');
    expect($fallbackStore->resolvedPaymentGatewayQrisMerchantName())->toBe('Server Merchant');
    expect($fallbackStore->resolvedPaymentGatewayCheckoutTtlMinutes())->toBe(30);
    expect($fallbackStore->paymentGatewayQrisConfigSource())->toBe('server');
});
