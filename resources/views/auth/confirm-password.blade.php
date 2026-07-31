<x-guest-layout>
    <x-slot name="title">Konfirmasi Kata Sandi</x-slot>
    <x-slot name="heading">Konfirmasi kata sandi</x-slot>
    <x-slot name="description">
        Bagian ini terlindungi. Masukkan kata sandi Anda sebelum melanjutkan.
    </x-slot>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <x-form-field label="Kata Sandi" name="password" required>
            <x-input id="password" type="password" name="password" required autocomplete="current-password" autofocus />
        </x-form-field>

        <x-button type="submit" class="w-full">Konfirmasi</x-button>
    </form>
</x-guest-layout>
