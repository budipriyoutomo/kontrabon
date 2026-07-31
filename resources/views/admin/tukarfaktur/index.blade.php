@php
    $filterKeys = ['search', 'start_date', 'end_date', 'start_bayar', 'end_bayar', 'pt_tujuan', 'status', 'perusahaan'];
    $adaFilter = request()->hasAny($filterKeys);
@endphp

<x-app-layout>
    <x-slot name="title">Tukar Faktur</x-slot>
    <x-slot name="header">Data Tukar Faktur</x-slot>

    <div class="space-y-4">

        <x-filter-card
            :action="route('admin.tukar-faktur.index')"
            :keys="$filterKeys"
            cols="sm:grid-cols-2 lg:grid-cols-4"
        >
            <x-slot name="actions">
                <x-button variant="success" :href="route('admin.tukar-faktur.export', request()->except('page'))">
                    <x-icon name="file-spreadsheet" />
                    Export Excel
                </x-button>
            </x-slot>

            <x-form-field label="Tanggal Tukar">
                <div class="flex items-center gap-2">
                    <x-input type="date" name="start_date" :value="request('start_date')" aria-label="Tanggal tukar dari" />
                    <span class="text-sm text-muted-foreground">s/d</span>
                    <x-input type="date" name="end_date" :value="request('end_date')" aria-label="Tanggal tukar sampai" />
                </div>
            </x-form-field>

            <x-form-field label="Tanggal Bayar">
                <div class="flex items-center gap-2">
                    <x-input type="date" name="start_bayar" :value="request('start_bayar')" aria-label="Tanggal bayar dari" />
                    <span class="text-sm text-muted-foreground">s/d</span>
                    <x-input type="date" name="end_bayar" :value="request('end_bayar')" aria-label="Tanggal bayar sampai" />
                </div>
            </x-form-field>

            <x-form-field label="PT Tujuan">
                <x-select name="pt_tujuan">
                    <option value="">Semua PT</option>
                    @foreach ($ptTujuanOptions as $option)
                        <option value="{{ $option }}" @selected(request('pt_tujuan') == $option)>
                            {{ $option }}
                        </option>
                    @endforeach
                </x-select>
            </x-form-field>

            <x-form-field label="Status">
                <x-select name="status">
                    <option value="">Semua status</option>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </x-select>
            </x-form-field>

            <x-form-field label="Perusahaan" class="sm:col-span-2">
                {{-- Daftar tetap, bukan pencarian ke server: filter ini juga
                     harus memuat nama lama dari data sebelum master ada. --}}
                <x-perusahaan-select
                    name="perusahaan"
                    :value="request('perusahaan')"
                    :options="$perusahaanOptions"
                    placeholder="Semua supplier"
                    ringkas
                />
            </x-form-field>

            <x-form-field label="Cari" class="sm:col-span-2">
                <div class="relative">
                    <x-icon
                        name="search"
                        class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
                    />
                    <x-input
                        type="text"
                        name="search"
                        :value="request('search')"
                        placeholder="Cari No Kwitansi"
                        class="pl-9"
                    />
                </div>
            </x-form-field>
        </x-filter-card>

        <x-card class="overflow-hidden">
            <x-table>
                <x-table.header>
                    <x-table.row>
                        <x-table.sort-head column="tanggal_tukar">Tgl Tukar</x-table.sort-head>
                        <x-table.sort-head column="pt_tujuan">PT Tujuan</x-table.sort-head>
                        <x-table.sort-head column="perusahaan_pengaju">Perusahaan</x-table.sort-head>
                        <x-table.sort-head column="no_kwitansi">No Kwitansi</x-table.sort-head>
                        <x-table.sort-head column="jumlah_rupiah" align="right" class="text-right">
                            Jumlah
                        </x-table.sort-head>
                        <x-table.sort-head column="status">Status</x-table.sort-head>
                        <x-table.sort-head column="tanggal_pembayaran">Tgl Bayar</x-table.sort-head>
                        <x-table.head class="text-right">Aksi</x-table.head>
                    </x-table.row>
                </x-table.header>

                <x-table.body>
                    @forelse ($data as $row)
                        <x-table.row>
                            <x-table.cell class="whitespace-nowrap text-muted-foreground">
                                {{ \Carbon\Carbon::parse($row->tanggal_tukar)->format('d M Y') }}
                            </x-table.cell>

                            <x-table.cell class="font-medium">{{ $row->pt_tujuan }}</x-table.cell>

                            <x-table.cell class="font-medium">{{ $row->perusahaan_pengaju }}</x-table.cell>

                            <x-table.cell class="font-mono text-xs">{{ $row->no_kwitansi }}</x-table.cell>

                            <x-table.cell class="whitespace-nowrap text-right tabular-nums">
                                Rp {{ number_format($row->jumlah_rupiah, 0, ',', '.') }}
                            </x-table.cell>

                            <x-table.cell>
                                <x-badge :status="$row->status" />

                                @if ($row->verified_at)
                                    <div class="mt-1 text-xs text-muted-foreground">
                                        oleh {{ optional($row->verifier)->name ?? '-' }}
                                        · {{ $row->verified_at->format('d/m/Y') }}
                                    </div>
                                @endif
                            </x-table.cell>

                            <x-table.cell class="whitespace-nowrap">
                                @if ($row->tanggal_pembayaran)
                                    {{ \Carbon\Carbon::parse($row->tanggal_pembayaran)->format('d M Y') }}
                                @else
                                    <span class="text-xs italic text-muted-foreground">Belum dibayar</span>
                                @endif
                            </x-table.cell>

                            <x-table.cell class="text-right">
                                <div class="flex justify-end gap-1">
                                    <x-button
                                        variant="ghost"
                                        size="sm"
                                        :href="route('admin.tukar-faktur.show', $row->id)"
                                    >
                                        <x-icon name="eye" />
                                        Detail
                                    </x-button>

                                    @if ($row->status === \App\Enums\TukarFakturStatus::Pending && auth()->user()->can('delete', $row))
                                        <x-button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                                            onclick="window.ui.confirmDelete('{{ route('admin.tukar-faktur.destroy', $row->id) }}', { title: 'Yakin hapus data?' })"
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
                            :colspan="8"
                            :title="$adaFilter ? 'Tidak ada data yang cocok' : 'Belum ada data tukar faktur'"
                            :description="$adaFilter ? 'Coba ubah atau kosongkan filter pencarian.' : null"
                        >
                            @if ($adaFilter)
                                <x-button variant="outline" size="sm" :href="route('admin.tukar-faktur.index')">
                                    <x-icon name="rotate-ccw" />
                                    Reset filter
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
