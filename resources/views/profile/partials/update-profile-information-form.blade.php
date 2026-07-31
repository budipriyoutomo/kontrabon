<x-card>
    <x-card.header>
        <x-card.title>Informasi Profil</x-card.title>
        <x-card.description>Perbarui nama dan alamat email akun Anda.</x-card.description>
    </x-card.header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <x-card.content class="space-y-4">
            <x-form-field label="Nama" name="name" required>
                <x-input
                    id="name"
                    name="name"
                    type="text"
                    :value="old('name', $user->name)"
                    required
                    autofocus
                    autocomplete="name"
                />
            </x-form-field>

            <x-form-field label="Email" name="email" required>
                <x-input
                    id="email"
                    name="email"
                    type="email"
                    :value="old('email', $user->email)"
                    required
                    autocomplete="username"
                />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <p class="text-sm text-muted-foreground">
                        Alamat email Anda belum terverifikasi.

                        <button
                            form="send-verification"
                            class="font-medium text-primary underline-offset-4 hover:underline"
                        >
                            Kirim ulang email verifikasi.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="text-sm font-medium text-success">
                            Tautan verifikasi baru sudah dikirim ke email Anda.
                        </p>
                    @endif
                @endif
            </x-form-field>
        </x-card.content>

        <x-card.footer class="gap-4">
            <x-button type="submit">Simpan</x-button>

            @if (session('status') === 'profile-updated')
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
