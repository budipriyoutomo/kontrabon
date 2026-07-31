<x-guest-layout>
    <x-slot name="title">Daftar</x-slot>
    <x-slot name="heading">Buat akun baru</x-slot>
    <x-slot name="description">Isi data berikut untuk membuat akun.</x-slot>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <x-form-field label="Nama" name="name" required>
            <x-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
        </x-form-field>

        <x-form-field label="Email" name="email" required>
            <x-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
        </x-form-field>

        <x-form-field label="Kata Sandi" name="password" required>
            <x-input id="password" type="password" name="password" required autocomplete="new-password" />
        </x-form-field>

        <x-form-field label="Konfirmasi Kata Sandi" name="password_confirmation" required>
            <x-input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
            />
        </x-form-field>

        <x-button type="submit" class="w-full">Daftar</x-button>

        <p class="text-center text-sm text-muted-foreground">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-medium text-primary underline-offset-4 hover:underline">
                Masuk
            </a>
        </p>
    </form>
</x-guest-layout>
