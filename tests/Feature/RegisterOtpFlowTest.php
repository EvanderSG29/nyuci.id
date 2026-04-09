<?php

use App\Models\User;
use App\Notifications\RegisterOtpNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('cache.default', 'array');
});

afterEach(function () {
    Carbon::setTestNow();
});

function registerOtpCacheKey(string $email): string
{
    return config('otp.store_key').'_'.md5($email);
}

test('registration sends otp and does not create the user before verification', function () {
    Notification::fake();

    $response = $this->post(route('register.store'), [
        'name' => 'Alya Laundry',
        'email' => 'alya@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response
        ->assertRedirect(route('register.otp.notice'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('users', [
        'email' => 'alya@example.com',
    ]);

    Notification::assertSentOnDemand(RegisterOtpNotification::class, function ($notification, $channels, $notifiable, $locale = null) {
        return $notifiable instanceof AnonymousNotifiable
            && $notifiable->routeNotificationFor('mail') === 'alya@example.com';
    });

    expect(Cache::get(registerOtpCacheKey('alya@example.com')))->toBeArray();
});

test('verified otp creates the user, logs them in, and redirects to setup toko', function () {
    Notification::fake();
    app('otp')->useGenerator(fn ($format = null, $length = null) => '123456');

    $this->post(route('register.store'), [
        'name' => 'Bima',
        'email' => 'bima@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('register.otp.notice'));

    $response = $this->post(route('register.otp.verify'), [
        'code' => '123456',
    ]);

    $response->assertRedirect(route('register.toko.create'));

    $user = User::query()->where('email', 'bima@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->email_verified_at)->not->toBeNull();

    $this->assertAuthenticatedAs($user);
});

test('wrong otp shows an error and does not create the user', function () {
    Notification::fake();
    app('otp')->useGenerator(fn ($format = null, $length = null) => '123456');

    $this->from(route('register.otp.notice'))
        ->post(route('register.store'), [
            'name' => 'Citra',
            'email' => 'citra@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $response = $this->from(route('register.otp.notice'))
        ->post(route('register.otp.verify'), [
            'code' => '000000',
        ]);

    $response
        ->assertRedirect(route('register.otp.notice'))
        ->assertSessionHasErrors('code');

    $this->assertGuest();
    $this->assertDatabaseMissing('users', [
        'email' => 'citra@example.com',
    ]);
});

test('expired otp clears pending state and keeps the user unregistered', function () {
    Notification::fake();
    app('otp')->useGenerator(fn ($format = null, $length = null) => '654321');

    $this->post(route('register.store'), [
        'name' => 'Dina',
        'email' => 'dina@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('register.otp.notice'));

    $cacheKey = registerOtpCacheKey('dina@example.com');
    $storedOtp = Cache::get($cacheKey);

    Cache::put(
        $cacheKey,
        [...$storedOtp, 'expires' => now()->subMinute()->toIso8601String()],
        now()->addMinute(),
    );

    $response = $this->post(route('register.otp.verify'), [
        'code' => '654321',
    ]);

    $response
        ->assertRedirect(route('register'))
        ->assertSessionHas('warning');

    $this->assertGuest();
    $this->assertDatabaseMissing('users', [
        'email' => 'dina@example.com',
    ]);
});

test('resend generates a new otp and invalidates the previous code', function () {
    Notification::fake();
    app('otp')->useGenerator(fn ($format = null, $length = null) => '111111');

    $this->post(route('register.store'), [
        'name' => 'Eka',
        'email' => 'eka@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('register.otp.notice'));

    Carbon::setTestNow(now()->addSeconds(61));
    app('otp')->useGenerator(fn ($format = null, $length = null) => '222222');

    $resendResponse = $this->from(route('register.otp.notice'))
        ->post(route('register.otp.resend'));

    $resendResponse
        ->assertRedirect(route('register.otp.notice'))
        ->assertSessionHas('success');

    $oldCodeResponse = $this->from(route('register.otp.notice'))
        ->post(route('register.otp.verify'), [
            'code' => '111111',
        ]);

    $oldCodeResponse
        ->assertRedirect(route('register.otp.notice'))
        ->assertSessionHasErrors('code');

    $newCodeResponse = $this->post(route('register.otp.verify'), [
        'code' => '222222',
    ]);

    $newCodeResponse->assertRedirect(route('register.toko.create'));
    $this->assertAuthenticated();
});

test('existing email stays invalid on the registration form', function () {
    User::factory()->create([
        'email' => 'fajar@example.com',
    ]);

    $response = $this->from(route('register'))
        ->post(route('register.store'), [
            'name' => 'Fajar',
            'email' => 'fajar@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $response
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors('email');
});

test('pending otp page can be refreshed while the otp is still active', function () {
    Notification::fake();

    $this->post(route('register.store'), [
        'name' => 'Gina',
        'email' => 'gina@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('register.otp.notice'));

    $this->get(route('register.otp.notice'))
        ->assertOk()
        ->assertSee('Masukkan kode OTP')
        ->assertSee('gina@example.com');

    $this->get(route('register.otp.notice'))
        ->assertOk()
        ->assertSee('Kirim ulang kode');
});
