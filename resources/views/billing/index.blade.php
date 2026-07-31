@php
    use App\Enums\TukarFakturStatus;
@endphp

<x-app-layout>
    <x-slot name="title">Billing</x-slot>
    <x-slot name="header">Billing</x-slot>

    <div class="space-y-4">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <x-alert variant="success" icon="circle-check" class="flex-1">
                <x-alert.description>
                    Hanya menampilkan pengajuan yang <strong>sudah terverifikasi</strong>.
                    <strong>{{ $jumlahSiapBilling }}</strong> dokumen siap diproses.
                </x-alert.description>
            </x-alert>

            <x-button variant="outline" :href="route('billing.rekap', request()->query())">
                <x-icon name="table-2" />
                Lihat Rekap
            </x-button>
        </div>

        @include('billing._ringkasan')

        @include('billing._filter', ['action' => route('billing.index')])

        <form method="POST" action="{{ route('billing.proses-massal') }}" x-data="{ terpilih: [] }" class="space-y-3">
            @csrf

            <x-button type="submit" x-bind:disabled="terpilih.length === 0">
                <x-icon name="hand-coins" />
                Proses billing terpilih (<span x-text="terpilih.length">0</span>)
            </x-button>

            <x-card class="overflow-hidden">
                <x-table>
                    <x-table.header>
                        <x-table.row>
                            <x-table.head class="w-10">
                                <x-checkbox
                                    x-on:change="terpilih = $event.target.checked
                                        ? Array.from(document.querySelectorAll('.cek-billing')).map(el => el.value)
                                        : []"
                                />
                                <span class="sr-only">Pilih semua baris</span>
                            </x-table.head>

                            <x-table.sort-head column="tanggal_pembayaran">Tgl Bayar</x-table.sort-head>
                            <x-table.sort-head column="pt_tujuan">PT Tujuan</x-table.sort-head>
                            <x-table.head>Supplier</x-table.head>
                            <x-table.head>No Kwitansi</x-table.head>
                            <x-table.sort-head column="jumlah_rupiah" align="right" class="text-right">
                                Jumlah
                            </x-table.sort-head>
                            <x-table.head>Status</x-table.head>
                            <x-table.head class="text-right">Aksi</x-table.head>
                        </x-table.row>
                    </x-table.header>

                    <x-table.body>
                        @forelse ($data as $row)
                            <x-table.row>
                                <x-table.cell>
                                    @if ($row->status === TukarFakturStatus::Verified)
                                        <x-checkbox
                                            class="cek-billing"
                                            name="ids[]"
                                            value="{{ $row->id }}"
                                            x-model="terpilih"
                                        />
                                    @endif
                                </x-table.cell>

                                <x-table.cell class="whitespace-nowrap font-medium">
                                    {{ $row->tanggal_pembayaran
                                        ? \Carbon\Carbon::parse($row->tanggal_pembayaran)->format('d/m/Y')
                                        : '-' }}
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

                                <x-table.cell>
                                    <x-badge :status="$row->status" />

                                    @if ($row->billed_at)
                                        <div class="mt-1 text-xs text-muted-foreground">
                                            oleh {{ optional($row->biller)->name ?? '-' }}
                                        </div>
                                    @endif
                                </x-table.cell>

                                <x-table.cell class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <x-button variant="ghost" size="sm" :href="route('admin.tukar-faktur.show', $row->id)">
                                            <x-icon name="eye" />
                                            Detail
                                        </x-button>

                                        @if ($row->status === TukarFakturStatus::Verified)
                                            {{-- formaction dipakai supaya tidak perlu <form> bersarang
                                                 di dalam form proses massal. --}}
                                            <x-button
                                                type="submit"
                                                variant="ghost"
                                                size="sm"
                                                formaction="{{ route('billing.proses', $row->id) }}"
                                                formmethod="POST"
                                            >
                                                <x-icon name="hand-coins" />
                                                Proses
                                            </x-button>
                                        @endif
                                    </div>
                                </x-table.cell>
                            </x-table.row>
                        @empty
                            <x-table.empty
                                :colspan="8"
                                icon="wallet"
                                title="Tidak ada data terverifikasi"
                                description="Belum ada dokumen terverifikasi yang cocok dengan filter."
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
