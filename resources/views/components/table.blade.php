{{--
    Tabel data ala shadcn/ui.

    Elemen <table> dibungkus div ber-overflow-x supaya tabel lebar menggeser
    dirinya sendiri, bukan menggeser seluruh halaman di layar kecil.

        <x-table>
            <x-table.header>
                <x-table.row>
                    <x-table.head>Nomor</x-table.head>
                </x-table.row>
            </x-table.header>
            <x-table.body>
                <x-table.row>
                    <x-table.cell>001</x-table.cell>
                </x-table.row>
            </x-table.body>
        </x-table>
--}}
<div class="relative w-full overflow-x-auto">
    <table {{ $attributes->twMerge('w-full caption-bottom text-sm') }}>
        {{ $slot }}
    </table>
</div>
