@php
    use App\Enums\TukarFakturStatus;

    $menungguVerifikasi = $statusTerpilih === TukarFakturStatus::EmailSent;
    $filterKeys = ['search', 'status', 'pt_tujuan', 'perusahaan', 'start_date', 'end_date'];
    $adaFilter = request()->hasAny($filterKeys);
    $jumlahKolom = $menungguVerifikasi ? 9 : 8;
@endphp

<x-app-layout>
    <x-slot name="title">Verifikasi</x-slot>
    <x-slot name="header">Verifikasi Tukar Faktur</x-slot>

    <div class="space-y-4">

        <x-alert variant="info" icon="info">
            <x-alert.description>
                Yang diverifikasi adalah data yang <strong>emailnya sudah terkirim</strong> ke supplier.
                Saat ini ada <strong>{{ $jumlahMenunggu }}</strong> data menunggu verifikasi.
            </x-alert.description>
        </x-alert>

        <x-filter-card :action="route('admin.verifikasi.index')" :keys="$filterKeys">
            <x-form-field label="Cari">
                <x-input
                    type="text"
                    name="search"
                    :value="request('search')"
                    placeholder="No kwitansi / supplier"
                />
            </x-form-field>

            <x-form-field label="Status">
                <x-select name="status">
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($statusTerpilih->value === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </x-select>
            </x-form-field>

            <x-form-field label="PT Tujuan">
                <x-select name="pt_tujuan">
                    <option value="">Semua PT</option>
                    @foreach ($ptTujuanOptions as $option)
                        <option value="{{ $option }}" @selected(request('pt_tujuan') === $option)>
                            {{ $option }}
                        </option>
                    @endforeach
                </x-select>
            </x-form-field>

            <x-form-field label="Supplier">
                <x-perusahaan-select
                    name="perusahaan"
                    :value="request('perusahaan')"
                    :options="$perusahaanOptions"
                    placeholder="Semua supplier"
                    ringkas
                />
            </x-form-field>

            <x-form-field label="Tanggal Tukar" class="sm:col-span-2 lg:col-span-2">
                <div class="flex items-center gap-2">
                    <x-input type="date" name="start_date" :value="request('start_date')" aria-label="Tanggal tukar dari" />
                    <span class="text-sm text-muted-foreground">s/d</span>
                    <x-input type="date" name="end_date" :value="request('end_date')" aria-label="Tanggal tukar sampai" />
                </div>
            </x-form-field>
        </x-filter-card>

        <form method="POST" action="{{ route('admin.verifikasi.bulk') }}" x-data="{ terpilih: [] }" class="space-y-3">
            @csrf

            @if ($menungguVerifikasi)
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <x-input
                        type="text"
                        name="verified_note"
                        maxlength="255"
                        placeholder="Catatan verifikasi (opsional)"
                        class="sm:w-80"
                    />

                    <x-button type="submit" variant="success" x-bind:disabled="terpilih.length === 0">
                        <x-icon name="badge-check" />
                        Verifikasi terpilih (<span x-text="terpilih.length">0</span>)
                    </x-button>
                </div>
            @endif

            <x-card class="overflow-hidden">
                <x-table>
                    <x-table.header>
                        <x-table.row>
                            @if ($menungguVerifikasi)
                                <x-table.head class="w-10">
                                    <x-checkbox
                                        x-on:change="terpilih = $event.target.checked
                                            ? Array.from(document.querySelectorAll('.cek-baris')).map(el => el.value)
                                            : []"
                                    />
                                    <span class="sr-only">Pilih semua baris</span>
                                </x-table.head>
                            @endif

                            <x-table.sort-head column="tanggal_tukar">Tanggal Tukar</x-table.sort-head>
                            <x-table.head>PT Tujuan</x-table.head>
                            <x-table.head>Supplier</x-table.head>
                            <x-table.head>No Kwitansi</x-table.head>
                            <x-table.sort-head column="jumlah_rupiah" align="right" class="text-right">
                                Jumlah
                            </x-table.sort-head>
                            <x-table.head>Tanggal Bayar</x-table.head>
                            <x-table.head>{{ $menungguVerifikasi ? 'Status' : 'Verifikasi' }}</x-table.head>
                            <x-table.head class="text-right">Aksi</x-table.head>
                        </x-table.row>
                    </x-table.header>

                    <x-table.body>
                        @forelse ($data as $row)
                            <x-table.row>
                                @if ($menungguVerifikasi)
                                    <x-table.cell>
                                        <x-checkbox
                                            class="cek-baris"
                                            name="ids[]"
                                            value="{{ $row->id }}"
                                            x-model="terpilih"
                                        />
                                    </x-table.cell>
                                @endif

                                <x-table.cell class="whitespace-nowrap text-muted-foreground">
                                    {{ \Carbon\Carbon::parse($row->tanggal_tukar)->format('d/m/Y') }}
                                </x-table.cell>

                                <x-table.cell class="text-muted-foreground">{{ $row->pt_tujuan }}</x-table.cell>

                                <x-table.cell>
                                    <div class="font-medium">{{ $row->perusahaan_pengaju }}</div>

                                    @if ($row->perusahaan?->top !== null)
                                        <div class="text-xs text-muted-foreground">
                                            TOP {{ $row->perusahaan->top }} hari
                                        </div>
                                    @endif
                                </x-table.cell>

                                <x-table.cell class="font-mono text-xs">{{ $row->no_kwitansi }}</x-table.cell>

                                <x-table.cell class="whitespace-nowrap text-right font-medium tabular-nums">
                                    Rp {{ number_format($row->jumlah_rupiah, 0, ',', '.') }}
                                </x-table.cell>

                                <x-table.cell class="whitespace-nowrap text-muted-foreground">
                                    {{ $row->tanggal_pembayaran
                                        ? \Carbon\Carbon::parse($row->tanggal_pembayaran)->format('d/m/Y')
                                        : '-' }}
                                </x-table.cell>

                                <x-table.cell>
                                    @if ($menungguVerifikasi)
                                        <x-badge :status="$row->status" />
                                    @else
                                        <div>{{ optional($row->verifier)->name ?? '-' }}</div>
                                        <div class="text-xs text-muted-foreground">
                                            {{ optional($row->verified_at)->format('d/m/Y H:i') ?? '-' }}
                                        </div>

                                        @if ($row->verified_note)
                                            <div class="text-xs italic text-muted-foreground">{{ $row->verified_note }}</div>
                                        @endif
                                    @endif
                                </x-table.cell>

                                <x-table.cell class="text-right">
                                    <x-button variant="ghost" size="sm" :href="route('admin.tukar-faktur.show', $row->id)">
                                        <x-icon name="eye" />
                                        Detail
                                    </x-button>
                                </x-table.cell>
                            </x-table.row>
                        @empty
                            <x-table.empty
                                :colspan="$jumlahKolom"
                                icon="badge-check"
                                :title="$menungguVerifikasi ? 'Tidak ada data yang menunggu verifikasi' : 'Belum ada data pada status ini'"
                                :description="$adaFilter ? 'Coba ubah atau kosongkan filter pencarian.' : null"
                            />
                        @endforelse
                    </x-table.body>
                </x-table>

                @if ($data->hasPages())
                    <div class="border-t px-4 py-3">
                        {{ $data->appends(request()->query())->links() }}
                    </div>
                @endif
            </x-card>
        </form>

    </div>
</x-app-layout>
