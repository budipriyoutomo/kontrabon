<x-app-layout>
    <x-slot name="title">Profil</x-slot>
    <x-slot name="header">Profil</x-slot>

    <div class="mx-auto max-w-3xl space-y-6">
        @include('profile.partials.update-profile-information-form')

        @include('profile.partials.update-password-form')

        @include('profile.partials.delete-user-form')
    </div>
</x-app-layout>
