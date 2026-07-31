<x-app-layout>
    <x-slot name="title">Edit Pengguna</x-slot>

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Manajemen Pengguna', 'url' => route('admin.users.index')],
            ['label' => $user->name],
        ]" />
    </x-slot>

    <div class="mx-auto max-w-4xl">
        <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
            @csrf
            @method('PUT')

            <x-card>
                <x-card.header>
                    <x-card.title>Data Pengguna</x-card.title>
                    <x-card.description>Perbarui informasi dan peran akun.</x-card.description>
                </x-card.header>

                <x-card.content>
                    @include('admin.users._form', ['user' => $user])
                </x-card.content>

                <x-card.footer class="justify-end gap-2">
                    <x-button type="button" variant="outline" :href="route('admin.users.index')">Batal</x-button>
                    <x-button type="submit">Simpan Perubahan</x-button>
                </x-card.footer>
            </x-card>
        </form>
    </div>
</x-app-layout>
