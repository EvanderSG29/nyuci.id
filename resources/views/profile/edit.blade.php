<x-app-layout title="Profil Saya" navbar-eyebrow="Akun pengguna">
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
