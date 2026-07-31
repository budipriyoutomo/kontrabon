@php
    $adaFilter = request()->hasAny(['search', 'status']);
@endphp

<x-app-layout>
    <x-slot name="title">Master Perusahaan</x-slot>
    <x-slot name="header">Master Data Perusahaan</x-slot>

    <div class="space-y-4">

        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <form
                method="GET"
                action="{{ route('admin.perusahaan.index') }}"
                class="flex w-full flex-col gap-2 sm:flex-row md:w-auto"
            >
                <div class="relative">
                    <x-icon
                        name="search"
                        class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
                    />
                    <x-input
                        type="text"
                        name="search"
                        :value="request('search')"
                        placeholder="Cari nama / kode / NPWP"
                        class="pl-9 sm:w-64"
                    />
                </div>

                <x-select name="status" class="sm:w-40">
                    <option value="">Semua status</option>
                    <option value="aktif" @selected(request('status') == 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(request('status') == 'nonaktif')>Nonaktif</option>
                </x-select>

                <div class="flex gap-2">
                    <x-button type="submit">Terapkan</x-button>

                    @if ($adaFilter)
                        <x-button variant="ghost" :href="route('admin.perusahaan.index')">
                            <x-icon name="rotate-ccw" />
                            Reset
                        </x-button>
                    @endif
                </div>
            </form>

            <x-button :href="route('admin.perusahaan.create')">
                <x-icon name="plus" />
                Tambah Perusahaan
            </x-button>
        </div>

        <x-card class="overflow-hidden">
            <x-table>
                <x-table.header>
                    <x-table.row>
                        <x-table.sort-head column="kode">Kode</x-table.sort-head>
                        <x-table.sort-head column="nama">Nama Perusahaan</x-table.sort-head>
                        <x-table.head>NPWP</x-table.head>
                        <x-table.head>TOP</x-table.head>
                        <x-table.head>PIC</x-table.head>
                        <x-table.head>Email</x-table.head>
                        <x-table.head class="text-center">Faktur</x-table.head>
                        <x-table.head>Status</x-table.head>
                        <x-table.head class="text-right">Aksi</x-table.head>
                    </x-table.row>
                </x-table.header>

                <x-table.body>
                    @forelse ($data as $row)
                        <x-table.row>
                            <x-table.cell class="whitespace-nowrap text-muted-foreground">
                                {{ $row->kode ?: '-' }}
                            </x-table.cell>

                            <x-table.cell>
                                <div class="font-medium">{{ $row->nama }}</div>

                                @if ($row->alamat)
                                    <div class="text-xs text-muted-foreground">{{ $row->alamat }}</div>
                                @endif
                            </x-table.cell>

                            <x-table.cell class="whitespace-nowrap text-muted-foreground">
                                {{ $row->npwp ?: '-' }}
                            </x-table.cell>

                            <x-table.cell class="whitespace-nowrap text-muted-foreground">
                                {{ $row->top !== null ? $row->top . ' hari' : '-' }}
                            </x-table.cell>

                            <x-table.cell class="text-muted-foreground">{{ $row->nama_pic ?: '-' }}</x-table.cell>

                            <x-table.cell class="text-muted-foreground">{{ $row->email ?: '-' }}</x-table.cell>

                            <x-table.cell class="text-center tabular-nums text-muted-foreground">
                                {{ $row->tukar_fakturs_count }}
                            </x-table.cell>

                            <x-table.cell>
                                <x-badge :variant="$row->is_active ? 'success' : 'muted'">
                                    {{ $row->is_active ? 'Aktif' : 'Nonaktif' }}
                                </x-badge>
                            </x-table.cell>

                            <x-table.cell class="text-right">
                                <div class="flex justify-end gap-1">
                                    <x-button variant="ghost" size="sm" :href="route('admin.perusahaan.show', $row->id)">
                                        <x-icon name="eye" />
                                        Detail
                                    </x-button>

                                    <x-button variant="ghost" size="sm" :href="route('admin.perusahaan.edit', $row->id)">
                                        <x-icon name="pencil" />
                                        Edit
                                    </x-button>

                                    @if ($row->tukar_fakturs_count === 0)
                                        <x-button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                                            onclick="window.ui.confirmDelete('{{ route('admin.perusahaan.destroy', $row->id) }}', { title: 'Yakin hapus perusahaan?' })"
                                        >
                                            <x-icon name="trash-2" />
                                            Hapus
                                        </x-button>
                                    @endif
                                </div>
                            </x-table.cell>
                        </x-table.row>
                    @empty
                        <x-table.empty
                            :colspan="9"
                            icon="building-2"
                            :title="$adaFilter ? 'Tidak ada data yang cocok' : 'Belum ada data perusahaan'"
                            :description="$adaFilter ? 'Coba ubah atau kosongkan filter pencarian.' : null"
                        >
                            @if (! $adaFilter)
                                <x-button size="sm" :href="route('admin.perusahaan.create')">
                                    <x-icon name="plus" />
                                    Tambah Perusahaan
                                </x-button>
                            @endif
                        </x-table.empty>
                    @endforelse
                </x-table.body>
            </x-table>

            @if ($data->hasPages())
                <div class="border-t px-4 py-3">
                    {{ $data->appends(request()->query())->links() }}
                </div>
            @endif
        </x-card>

    </div>
</x-app-layout>
