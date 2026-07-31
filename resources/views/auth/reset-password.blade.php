<x-guest-layout>
    <x-slot name="title">Atur Ulang Kata Sandi</x-slot>
    <x-slot name="heading">Atur ulang kata sandi</x-slot>
    <x-slot name="description">Buat kata sandi baru untuk akun Anda.</x-slot>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-form-field label="Email" name="email" required>
            <x-input
                id="email"
                type="email"
                name="email"
                :value="old('email', $request->email)"
                required
                autofocus
                autocomplete="username"
            />
        </x-form-field>

        <x-form-field label="Kata Sandi Baru" name="password" required>
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

        <x-button type="submit" class="w-full">Simpan Kata Sandi</x-button>
    </form>
</x-guest-layout>
