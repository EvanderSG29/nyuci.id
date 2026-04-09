<x-guest-layout title="Verifikasi OTP">
    <div
        x-data="{
            digits: Array.from({ length: 6 }, () => ''),
            expiresAt: @js($expiresAt->toIso8601String()),
            resendAvailableAt: @js($resendAvailableAt?->toIso8601String()),
            remainingSeconds: 0,
            resendSeconds: 0,
            init() {
                this.syncTimers();

                window.setInterval(() => {
                    this.syncTimers();
                }, 1000);
            },
            syncTimers() {
                const now = Date.now();
                const expires = new Date(this.expiresAt).getTime();

                this.remainingSeconds = Math.max(0, Math.floor((expires - now) / 1000));

                if (this.resendAvailableAt) {
                    const resend = new Date(this.resendAvailableAt).getTime();
                    this.resendSeconds = Math.max(0, Math.floor((resend - now) / 1000));
                } else {
                    this.resendSeconds = 0;
                }
            },
            get code() {
                return this.digits.join('');
            },
            get timerLabel() {
                const minutes = String(Math.floor(this.remainingSeconds / 60)).padStart(2, '0');
                const seconds = String(this.remainingSeconds % 60).padStart(2, '0');

                return `${minutes}:${seconds}`;
            },
            get resendLabel() {
                return `Kirim ulang tersedia dalam ${this.resendSeconds} detik`;
            },
            focusInput(index) {
                const inputs = this.$root.querySelectorAll('[data-otp-input]');
                inputs[index]?.focus();
                inputs[index]?.select();
            },
            onInput(event, index) {
                const value = event.target.value.replace(/\\D/g, '').slice(-1);
                this.digits[index] = value;
                event.target.value = value;

                if (value && index < this.digits.length - 1) {
                    this.focusInput(index + 1);
                }
            },
            onBackspace(event, index) {
                if (event.target.value !== '') {
                    this.digits[index] = '';
                    event.target.value = '';
                    return;
                }

                if (index > 0) {
                    this.focusInput(index - 1);
                }
            },
            onPaste(event) {
                const pasted = (event.clipboardData?.getData('text') ?? '').replace(/\\D/g, '').slice(0, this.digits.length);

                if (!pasted) {
                    return;
                }

                pasted.split('').forEach((digit, index) => {
                    this.digits[index] = digit;
                });

                this.focusInput(Math.min(pasted.length, this.digits.length) - 1);
            },
        }"
        x-init="init()"
        @paste.prevent="onPaste($event)"
        class="space-y-6"
    >
        <div class="space-y-3">
            <div class="inline-flex items-center rounded-full border border-[var(--border-main)] bg-[var(--bg-surface)] px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--primary-ink)]">
                Email Verification
            </div>

            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">Masukkan kode OTP</h1>
                <p class="mt-2 text-sm leading-6 text-[var(--text-muted)]">
                    Kami sudah mengirim 6 digit kode verifikasi ke
                    <span class="font-semibold text-[var(--text-strong)]">{{ $email }}</span>.
                </p>
            </div>
        </div>

        <div class="rounded-3xl border border-[var(--border-main)] bg-[var(--bg-surface)] p-5 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--text-muted)]">Masa berlaku OTP</p>
                    <p class="mt-2 text-2xl font-semibold text-[var(--text-strong)]" x-text="timerLabel"></p>
                </div>

                <div class="rounded-2xl border border-[var(--border-main)] bg-[var(--bg-card)] px-4 py-3 text-right">
                    <p class="text-xs text-[var(--text-muted)]">Resend cooldown</p>
                    <p class="mt-1 text-sm font-medium text-[var(--text-strong)]" x-text="resendSeconds > 0 ? resendLabel : 'Anda sudah bisa mengirim ulang kode.'"></p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('register.otp.verify') }}" class="space-y-5">
            @csrf

            <input type="hidden" name="code" :value="code">

            <div>
                <label class="mb-3 block text-sm font-medium text-[var(--text-strong)]">Kode verifikasi</label>
                <div class="grid grid-cols-6 gap-2 sm:gap-3">
                    <template x-for="(digit, index) in digits" :key="index">
                        <input
                            data-otp-input
                            type="text"
                            maxlength="1"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            class="h-14 rounded-2xl border border-[var(--border-main)] bg-[var(--bg-card)] text-center text-xl font-semibold text-[var(--text-strong)] shadow-sm transition focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)]/20"
                            x-model="digits[index]"
                            @input="onInput($event, index)"
                            @keydown.backspace.prevent="onBackspace($event, index)"
                            @keydown.left.prevent="focusInput(Math.max(0, index - 1))"
                            @keydown.right.prevent="focusInput(Math.min(digits.length - 1, index + 1))"
                        >
                    </template>
                </div>

                <x-input-error :messages="$errors->get('code')" class="mt-3" />
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('register') }}" class="text-sm font-medium text-[var(--text-muted)] underline transition hover:text-[var(--text-strong)]">
                    Kembali ke form pendaftaran
                </a>

                <x-primary-button class="justify-center sm:min-w-40">
                    Verifikasi
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('register.otp.resend') }}" class="rounded-3xl border border-dashed border-[var(--border-main)] bg-[var(--bg-surface)] p-5">
            @csrf

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[var(--text-strong)]">Belum menerima email?</p>
                    <p class="mt-1 text-sm text-[var(--text-muted)]">
                        Anda bisa meminta kode baru setelah jeda pengiriman selesai.
                    </p>
                </div>

                <flux:button type="submit" variant="filled" color="zinc" x-bind:disabled="resendSeconds > 0">
                    Kirim ulang kode
                </flux:button>
            </div>
        </form>
    </div>
</x-guest-layout>
