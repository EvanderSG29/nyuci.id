<?php

namespace App\Http\Controllers;

use App\Support\Auth\RegisterOtpState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use SadiqSalau\LaravelOtp\Facades\Otp;
use Throwable;

class OtpController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $email = RegisterOtpState::email($request->session());
        $expiresAt = RegisterOtpState::expiresAt($request->session());

        if (! $email || ! $expiresAt) {
            return redirect()
                ->route('register')
                ->with('warning', 'Silakan lengkapi formulir pendaftaran terlebih dahulu.');
        }

        if (now()->greaterThanOrEqualTo($expiresAt)) {
            RegisterOtpState::clear($request->session());

            return redirect()
                ->route('register')
                ->with('warning', 'Kode OTP sudah kedaluwarsa. Silakan daftar ulang untuk meminta kode baru.');
        }

        return view('auth.verify-register-otp', [
            'email' => $email,
            'expiresAt' => $expiresAt,
            'resendAvailableAt' => RegisterOtpState::resendAvailableAt(
                $request->session(),
                (int) config('otp.resend_cooldown', 60),
            ),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $email = RegisterOtpState::email($request->session());

        if (! $email) {
            return redirect()
                ->route('register')
                ->with('warning', 'Sesi verifikasi OTP tidak ditemukan. Silakan daftar kembali.');
        }

        try {
            $validated = Validator::make(
                $request->all(),
                [
                    'code' => ['required', 'string', 'size:'.config('otp.length', 6)],
                ],
                [
                    'code.size' => 'Panjang kode OTP tidak sesuai.',
                ],
            )->validate();

            $otp = Otp::identifier($email)->attempt($validated['code']);

            if ($otp['status'] !== Otp::OTP_PROCESSED) {
                if (in_array($otp['status'], [Otp::OTP_EMPTY, Otp::OTP_EXPIRED], true)) {
                    RegisterOtpState::clear($request->session());

                    return redirect()
                        ->route('register')
                        ->with('warning', $this->otpMessage($otp['status']));
                }

                return back()
                    ->withInput()
                    ->withErrors(['code' => $this->otpMessage($otp['status'])])
                    ->with('warning', $this->otpMessage($otp['status']));
            }

            RegisterOtpState::clear($request->session());
            $request->session()->regenerate();

            return redirect()
                ->route('register.toko.create')
                ->with('success', 'Email berhasil diverifikasi. Lengkapi data toko Anda.');
        } catch (ValidationException $exception) {
            return back()
                ->withErrors($exception->errors())
                ->withInput();
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('warning', 'Verifikasi OTP gagal diproses. Silakan coba lagi.');
        }
    }

    public function resend(Request $request): RedirectResponse
    {
        $email = RegisterOtpState::email($request->session());

        if (! $email) {
            return redirect()
                ->route('register')
                ->with('warning', 'Sesi verifikasi OTP tidak ditemukan. Silakan daftar kembali.');
        }

        $resendAvailableAt = RegisterOtpState::resendAvailableAt(
            $request->session(),
            (int) config('otp.resend_cooldown', 60),
        );

        if ($resendAvailableAt && now()->lt($resendAvailableAt)) {
            return back()->with(
                'warning',
                'Anda bisa mengirim ulang OTP dalam '.$resendAvailableAt->diffInSeconds(now()).' detik.'
            );
        }

        try {
            $otp = Otp::identifier($email)->update();

            if ($otp['status'] !== Otp::OTP_SENT) {
                RegisterOtpState::clear($request->session());

                return redirect()
                    ->route('register')
                    ->with('warning', $this->otpMessage($otp['status']));
            }

            RegisterOtpState::put(
                $request->session(),
                $email,
                now()->addMinutes((int) config('otp.expires', 15)),
                now(),
            );

            return back()->with('success', 'Kode OTP berhasil dikirim ulang ke email Anda.');
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('warning', 'Gagal mengirim ulang OTP. Silakan coba lagi.');
        }
    }

    protected function otpMessage(string $status): string
    {
        return match ($status) {
            Otp::OTP_SENT => 'Kode OTP berhasil dikirim ke email Anda.',
            Otp::OTP_PROCESSED => 'OTP valid.',
            Otp::OTP_EMPTY => 'OTP tidak ditemukan. Silakan daftar ulang untuk meminta kode baru.',
            Otp::OTP_EXPIRED => 'Kode OTP sudah kedaluwarsa. Silakan daftar ulang untuk meminta kode baru.',
            Otp::OTP_MISMATCHED => 'Kode OTP yang Anda masukkan salah.',
            default => 'Terjadi kesalahan pada proses OTP.',
        };
    }
}
