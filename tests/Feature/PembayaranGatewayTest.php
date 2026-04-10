<?php

use App\Models\Jasa;
use App\Models\Klien;
use App\Models\Laundry;
use App\Models\Pembayaran;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createGatewayOwner(): User
{
    $user = User::factory()->create();

    Toko::create([
        'user_id' => $user->id,
        'nama_toko' => 'Nyuci Gateway Test',
        'alamat' => 'Jl. Gateway No. 1',
        'no_hp' => '081200000010',
    ]);

    return $user->fresh();
}

function createGatewayLaundry(Toko $toko, array $overrides = []): array
{
    $klien = Klien::create(array_merge([
        'toko_id' => $toko->id,
        'nama_klien' => 'Gateway Customer',
        'alamat_klien' => 'Jl. Payment',
        'no_hp_klien' => '081200000011',
    ], $overrides['klien'] ?? []));

    $jasa = Jasa::create(array_merge([
        'toko_id' => $toko->id,
        'nama_jasa' => 'cuci',
        'satuan' => 'kg',
        'harga' => 10000,
    ], $overrides['jasa'] ?? []));

    $laundry = Laundry::create(array_merge([
        'toko_id' => $toko->id,
        'klien_id' => $klien->id,
        'jasa_id' => $jasa->id,
        'qty' => 2,
        'status' => 'belum_selesai',
        'tanggal_dimulai' => '2026-04-09',
        'ets_selesai' => '2026-04-10',
        'nama' => $klien->nama_klien,
        'no_hp' => $klien->no_hp_klien,
        'berat' => 2,
        'satuan' => '2 kg',
        'tanggal' => '2026-04-09',
        'layanan' => $jasa->nama_jasa,
        'jenis_jasa' => $jasa->nama_jasa,
        'estimasi_selesai' => '2026-04-10',
        'is_taken' => false,
    ], $overrides['laundry'] ?? []));

    return [$klien, $jasa, $laundry];
}

function configureGatewayStatic(): void
{
    config()->set('payment_gateway.driver', 'qris_static');
    config()->set('payment_gateway.checkout_ttl_minutes', 30);
    config()->set('payment_gateway.qris_static.payload', '0002010102115802ID6304ABCD');
    config()->set('payment_gateway.qris_static.merchant_name', 'Nyuci Gateway');
}

test('gateway issue creates QRIS checkout session and stores static gateway data', function () {
    configureGatewayStatic();

    $user = createGatewayOwner();
    [$klien, $jasa, $laundry] = createGatewayLaundry($user->toko);

    $payment = Pembayaran::create([
        'klien_id' => $klien->id,
        'laundry_id' => $laundry->id,
        'total' => 20000,
        'total_biaya' => 20000,
        'metode_pembayaran' => 'cash',
        'tgl_pembayaran' => null,
        'catatan' => null,
        'status' => 'belum_bayar',
    ]);

    $this
        ->actingAs($user)
        ->post(route('pembayaran.gateway.issue', $payment))
        ->assertRedirect();

    $payment->refresh();

    expect($payment->metode_pembayaran)->toBe('qris');
    expect($payment->gateway_provider)->toBe('qris_static');
    expect($payment->gateway_reference)->not()->toBeEmpty();
    expect($payment->gateway_invoice_id)->not()->toBeEmpty();
    expect($payment->gateway_token)->not()->toBeEmpty();
    expect($payment->gateway_payment_url)->toBeNull();
    expect($payment->gateway_qr_image)->toStartWith('data:image/svg+xml;base64,');
    expect($payment->gateway_status)->toBe('pending');
    expect($payment->gateway_expires_at)->not()->toBeNull();
    expect($payment->gateway_payload)->toBeArray();
    expect($payment->gateway_payload['merchant_name'])->toBe('Nyuci Gateway');
    expect($payment->gateway_payload['amount'])->toBe(20000);
    expect($payment->gateway_payload['qris_text'])->toContain('540520000');
});

test('gateway checkout page renders payment summary and qr image', function () {
    configureGatewayStatic();

    $user = createGatewayOwner();
    [$klien, $jasa, $laundry] = createGatewayLaundry($user->toko);

    $payment = Pembayaran::create([
        'klien_id' => $klien->id,
        'laundry_id' => $laundry->id,
        'total' => 20000,
        'total_biaya' => 20000,
        'metode_pembayaran' => 'cash',
        'tgl_pembayaran' => null,
        'catatan' => null,
        'status' => 'belum_bayar',
    ]);

    $this
        ->actingAs($user)
        ->post(route('pembayaran.gateway.issue', $payment))
        ->assertRedirect();

    $payment->refresh();

    $this
        ->get(route('pembayaran.gateway.checkout', ['pembayaran' => $payment->id, 'token' => $payment->gateway_token]))
        ->assertOk()
        ->assertSee('Checkout QRIS')
        ->assertSee('Nyuci Gateway')
        ->assertSee('Muat Ulang Status')
        ->assertSee('data:image/svg+xml;base64,', false);
});

test('gateway sync reflects manual payment confirmation', function () {
    configureGatewayStatic();

    $user = createGatewayOwner();
    [$klien, $jasa, $laundry] = createGatewayLaundry($user->toko);

    $payment = Pembayaran::create([
        'klien_id' => $klien->id,
        'laundry_id' => $laundry->id,
        'total' => 20000,
        'total_biaya' => 20000,
        'metode_pembayaran' => 'cash',
        'tgl_pembayaran' => null,
        'catatan' => null,
        'status' => 'belum_bayar',
    ]);

    $this
        ->actingAs($user)
        ->post(route('pembayaran.gateway.issue', $payment))
        ->assertRedirect();

    $payment->refresh();

    $this
        ->post(route('pembayaran.gateway.sync', ['pembayaran' => $payment->id, 'token' => $payment->gateway_token]))
        ->assertRedirect();

    $payment->refresh();

    expect($payment->gateway_status)->toBe('pending');
    expect($payment->status)->toBe('belum_bayar');

    $payment->update([
        'status' => 'sudah_bayar',
        'tgl_pembayaran' => '2026-04-09',
    ]);

    $this
        ->post(route('pembayaran.gateway.sync', ['pembayaran' => $payment->id, 'token' => $payment->gateway_token]))
        ->assertRedirect();

    $payment->refresh();

    expect($payment->status)->toBe('sudah_bayar');
    expect($payment->metode_pembayaran)->toBe('qris');
    expect($payment->gateway_status)->toBe('paid');
    expect($payment->gateway_customer_name)->toBe('Gateway Customer');
    expect($payment->gateway_method_by)->toBe('QRIS Statis');
    expect($payment->gateway_paid_at)->not()->toBeNull();
});
