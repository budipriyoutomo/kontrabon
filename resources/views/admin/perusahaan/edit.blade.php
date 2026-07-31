<x-app-layout>
    <x-slot name="title">Edit Perusahaan</x-slot>

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Master Perusahaan', 'url' => route('admin.perusahaan.index')],
            ['label' => $perusahaan->nama, 'url' => route('admin.perusahaan.show', $perusahaan->id)],
            ['label' => 'Edit'],
        ]" />
    </x-slot>

    <div class="mx-auto max-w-4xl">
        <form method="POST" action="{{ route('admin.perusahaan.update', $perusahaan->id) }}">
            @csrf
            @method('PUT')

            <x-card>
                <x-card.header>
                    <x-card.title>Data Perusahaan</x-card.title>
                    <x-card.description>Perbarui informasi supplier.</x-card.description>
                </x-card.header>

                <x-card.content>
                    @include('admin.perusahaan._form', ['perusahaan' => $perusahaan])
                </x-card.content>

                <x-card.footer class="justify-end gap-2">
                    <x-button type="button" variant="outline" :href="route('admin.perusahaan.index')">
                        Batal
                    </x-button>

                    <x-button type="submit">Simpan Perubahan</x-button>
                </x-card.footer>
            </x-card>
        </form>
    </div>
</x-app-layout>
