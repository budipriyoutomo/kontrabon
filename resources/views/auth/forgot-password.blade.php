<x-guest-layout>
    <x-slot name="title">Lupa Kata Sandi</x-slot>
    <x-slot name="heading">Lupa kata sandi?</x-slot>
    <x-slot name="description">
        Masukkan email Anda dan kami kirimkan tautan untuk membuat kata sandi baru.
    </x-slot>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <x-form-field label="Email" name="email" required>
            <x-input id="email" type="email" name="email" :value="old('email')" required autofocus />
        </x-form-field>

        <x-button type="submit" class="w-full">Kirim Tautan Reset</x-button>

        <p class="text-center text-sm text-muted-foreground">
            <a href="{{ route('login') }}" class="font-medium text-primary underline-offset-4 hover:underline">
                Kembali ke halaman masuk
            </a>
        </p>
    </form>
</x-guest-layout>
