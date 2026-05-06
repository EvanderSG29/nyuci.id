<div class="space-y-6">
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
