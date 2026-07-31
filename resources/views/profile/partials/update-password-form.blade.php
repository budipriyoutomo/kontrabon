<x-card>
    <x-card.header>
        <x-card.title>Ubah Kata Sandi</x-card.title>
        <x-card.description>
            Pakai kata sandi yang panjang dan acak agar akun tetap aman.
        </x-card.description>
    </x-card.header>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <x-card.content class="space-y-4">
            <x-form-field
                label="Kata Sandi Saat Ini"
                for="update_password_current_password"
                :messages="$errors->updatePassword->get('current_password')"
            >
                <x-input
                    id="update_password_current_password"
                    name="current_password"
                    type="password"
                    autocomplete="current-password"
                />
            </x-form-field>

            <x-form-field
                label="Kata Sandi Baru"
                for="update_password_password"
                :messages="$errors->updatePassword->get('password')"
            >
                <x-input
                    id="update_password_password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                />
            </x-form-field>

            <x-form-field
                label="Konfirmasi Kata Sandi"
                for="update_password_password_confirmation"
                :messages="$errors->updatePassword->get('password_confirmation')"
            >
                <x-input
                    id="update_password_password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                />
            </x-form-field>
        </x-card.content>

        <x-card.footer class="gap-4">
            <x-button type="submit">Simpan</x-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-muted-foreground"
                >Tersimpan.</p>
            @endif
        </x-card.footer>
    </form>
</x-card>
