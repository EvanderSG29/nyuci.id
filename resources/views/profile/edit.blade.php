<x-app-layout title="Profil Saya">
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-medium text-[var(--text-muted)]">Akun pengguna</p>
            <h2 class="text-2xl font-semibold tracking-tight text-[var(--text-strong)]">Profil Saya</h2>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-card class="p-4 sm:p-6">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </x-card>

            <x-card class="p-4 sm:p-6">
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </x-card>

            <x-card class="p-4 sm:p-6">
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
