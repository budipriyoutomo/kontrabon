<x-guest-layout>
    <x-slot name="title">Masuk</x-slot>
    <x-slot name="heading">Masuk ke akun</x-slot>
    <x-slot name="description">Masukkan email dan kata sandi Anda untuk melanjutkan.</x-slot>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <x-form-field label="Email" name="email" required>
            <x-input
                id="email"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
            />
        </x-form-field>

        <x-form-field label="Kata Sandi" name="password" required>
            <x-input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
            />
        </x-form-field>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="flex items-center gap-2 text-sm text-muted-foreground">
                <x-checkbox id="remember_me" name="remember" />
                Ingat saya
            </label>

            @if (Route::has('password.request'))
                <a
                    href="{{ route('password.request') }}"
                    class="text-sm font-medium text-primary underline-offset-4 hover:underline"
                >
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        <x-button type="submit" class="w-full">Masuk</x-button>
    </form>
</x-guest-layout>
