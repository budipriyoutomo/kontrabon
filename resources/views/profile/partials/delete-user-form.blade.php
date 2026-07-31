<x-card class="border-destructive/40">
    <x-card.header>
        <x-card.title class="text-destructive">Hapus Akun</x-card.title>
        <x-card.description>
            Setelah akun dihapus, seluruh data terkait ikut hilang permanen. Unduh dulu data
            yang masih Anda butuhkan sebelum melanjutkan.
        </x-card.description>
    </x-card.header>

    <x-card.content>
        <x-button
            type="button"
            variant="destructive"
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        >
            <x-icon name="trash-2" />
            Hapus Akun
        </x-button>
    </x-card.content>

    <x-dialog name="confirm-user-deletion" max-width="lg" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')

            <x-dialog.header>
                <x-dialog.title>Yakin ingin menghapus akun Anda?</x-dialog.title>
                <x-dialog.description>
                    Seluruh data akun akan dihapus permanen. Masukkan kata sandi untuk mengonfirmasi.
                </x-dialog.description>
            </x-dialog.header>

            <x-dialog.content>
                <x-form-field
                    label="Kata Sandi"
                    for="password"
                    :messages="$errors->userDeletion->get('password')"
                >
                    <x-input id="password" name="password" type="password" placeholder="Kata sandi" />
                </x-form-field>
            </x-dialog.content>

            <x-dialog.footer>
                <x-button type="button" variant="outline" x-on:click="$dispatch('close')">Batal</x-button>
                <x-button type="submit" variant="destructive">Hapus Akun</x-button>
            </x-dialog.footer>
        </form>
    </x-dialog>
</x-card>
