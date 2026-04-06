<x-app-layout title="Profil">
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-[var(--text-strong)]">
            {{ __('Profil') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-[var(--border-main)] bg-[var(--bg-card)] p-4 shadow-sm sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="rounded-3xl border border-[var(--border-main)] bg-[var(--bg-card)] p-4 shadow-sm sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="rounded-3xl border border-[var(--border-main)] bg-[var(--bg-card)] p-4 shadow-sm sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
