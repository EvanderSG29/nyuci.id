<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Fortify\PasswordValidationRules;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Otp\RegisterUserOtp;
use App\Support\Auth\RegisterOtpState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use SadiqSalau\LaravelOtp\Facades\Otp;
use Throwable;

class RegisteredUserController extends Controller
{
    use PasswordValidationRules;

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'password' => $this->passwordRules(),
        ])->validate();

        $email = Str::lower(trim($validated['email']));
        $sentAt = now();
        $expiresAt = $sentAt->copy()->addMinutes((int) config('otp.expires', 15));

        try {
            Otp::identifier($email)->send(
                new RegisterUserOtp(
                    name: $validated['name'],
                    email: $email,
                    passwordHash: Hash::make($validated['password']),
                ),
                Notification::route('mail', $email),
            );
        } catch (Throwable $exception) {
            Otp::identifier($email)->clear();
            report($exception);

            return back()
                ->withInput([
                    'name' => $validated['name'],
                    'email' => $email,
                ])
                ->with('warning', 'Kode OTP gagal dikirim. Periksa konfigurasi email lalu coba lagi.');
        }

        RegisterOtpState::put($request->session(), $email, $expiresAt, $sentAt);

        return redirect()
            ->route('register.otp.notice')
            ->with('success', 'Kode OTP berhasil dikirim ke email Anda. Masukkan kode untuk menyelesaikan pendaftaran.');
    }
}
