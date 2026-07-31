<x-app-layout>
    <x-slot name="title">Rekap Pembayaran</x-slot>

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Billing', 'url' => route('billing.index')],
            ['label' => 'Rekap Jadwal Pembayaran'],
        ]" />
    </x-slot>

    <div class="space-y-4">

        <div class="flex justify-end">
            <x-button variant="outline" :href="route('billing.index', request()->query())">
                <x-icon name="list" />
                Lihat Daftar Dokumen
            </x-button>
        </div>

        @include('billing._ringkasan')

        @include('billing._filter', ['action' => route('billing.rekap')])

        @forelse ($rekap as $ptTujuan => $baris)
            @php
                $totalPt = $baris->sum('total_rupiah');
                $dokumenPt = $baris->sum('jumlah_dokumen');
            @endphp

            <x-card class="overflow-hidden">
                <x-card.header class="flex-row flex-wrap items-center justify-between gap-3 space-y-0 border-b">
                    <div class="space-y-1">
                        <x-card.title>{{ $ptTujuan }}</x-card.title>
                        <x-card.description>{{ $dokumenPt }} dokumen</x-card.description>
                    </div>

                    <p class="text-lg font-semibold tabular-nums tracking-tight">
                        Rp {{ number_format($totalPt, 0, ',', '.') }}
                    </p>
                </x-card.header>

                <x-table>
                    <x-table.header>
                        <x-table.row>
                            <x-table.head class="w-10"><span class="sr-only">Buka rincian</span></x-table.head>
                            <x-table.head>Tanggal Bayar</x-table.head>
                            <x-table.head class="text-center">Dokumen</x-table.head>
                            <x-table.head class="text-right">Subtotal</x-table.head>
                        </x-table.row>
                    </x-table.header>

                    @foreach ($baris as $item)
                        @php
                            $rincian = $dokumen[$item->kunci] ?? collect();
                        @endphp

                        {{-- Satu <tbody> per baris rekap: baris ringkas dan baris
                             rinciannya jadi satu kesatuan yang berbagi state buka,
                             sehingga tiap tanggal bisa dibentangkan sendiri-sendiri. --}}
                        <x-table.body x-data="{ buka: false }">
                            <x-table.row
                                class="cursor-pointer"
                                x-on:click="buka = ! buka"
                                x-bind:aria-expanded="buka"
                            >
                                <x-table.cell class="pr-0">
                                    <x-icon
                                        name="chevron-right"
                                        class="size-4 text-muted-foreground transition-transform"
                                        x-bind:class="buka && 'rotate-90'"
                                    />
                                </x-table.cell>

                                <x-table.cell class="whitespace-nowrap">
                                    {{ $item->tanggal_pembayaran
                                        ? \Carbon\Carbon::parse($item->tanggal_pembayaran)->format('d F Y')
                                        : 'Belum ada tanggal' }}
                                </x-table.cell>

                                <x-table.cell class="text-center text-muted-foreground tabular-nums">
                                    {{ $item->jumlah_dokumen }}
                                </x-table.cell>

                                <x-table.cell class="whitespace-nowrap text-right font-medium tabular-nums">
                                    Rp {{ number_format($item->total_rupiah, 0, ',', '.') }}
                                </x-table.cell>
                            </x-table.row>

                            <x-table.row x-show="buka" x-cloak class="hover:bg-transparent">
                                <x-table.cell colspan="4" class="bg-muted/40 p-0">
                                    <div class="overflow-x-auto px-4 py-3">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="border-b text-xs uppercase tracking-wide text-muted-foreground">
                                                    <th class="py-2 pr-4 text-left font-medium">No Kwitansi</th>
                                                    <th class="py-2 pr-4 text-left font-medium">Supplier</th>
                                                    <th class="py-2 pr-4 text-left font-medium">Tgl Tukar</th>
                                                    <th class="py-2 pr-4 text-left font-medium">Status</th>
                                                    <th class="py-2 text-right font-medium">Jumlah</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @forelse ($rincian as $dok)
                                                    <tr class="border-b last:border-0">
                                                        <td class="whitespace-nowrap py-2 pr-4 font-mono text-xs">
                                                            {{ $dok->no_kwitansi }}
                                                        </td>

                                                        <td class="py-2 pr-4">
                                                            <div class="font-medium">{{ $dok->perusahaan_pengaju }}</div>

                                                            @if ($dok->perusahaan?->top !== null)
                                                                <div class="text-xs text-muted-foreground">
                                                                    TOP {{ $dok->perusahaan->top }} hari
                                                                </div>
                                                            @endif
                                                        </td>

                                                        <td class="whitespace-nowrap py-2 pr-4 text-muted-foreground">
                                                            {{ $dok->tanggal_tukar
                                                                ? \Carbon\Carbon::parse($dok->tanggal_tukar)->format('d/m/Y')
                                                                : '-' }}
                                                        </td>

                                                        <td class="py-2 pr-4">
                                                            <x-badge :status="$dok->status" />
                                                        </td>

                                                        <td class="whitespace-nowrap py-2 text-right font-medium tabular-nums">
                                                            Rp {{ number_format($dok->jumlah_rupiah, 0, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="py-3 text-center text-muted-foreground">
                                                            Tidak ada dokumen pada baris ini.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>

                                        <div class="mt-3 flex justify-end">
                                            <x-button
                                                variant="outline"
                                                size="sm"
                                                :href="route('billing.index', array_merge(request()->except('page'), [
                                                    'pt_tujuan' => $item->pt_tujuan,
                                                    'start_bayar' => $item->tanggal_pembayaran
                                                        ? \Carbon\Carbon::parse($item->tanggal_pembayaran)->toDateString()
                                                        : null,
                                                    'end_bayar' => $item->tanggal_pembayaran
                                                        ? \Carbon\Carbon::parse($item->tanggal_pembayaran)->toDateString()
                                                        : null,
                                                ]))"
                                            >
                                                <x-icon name="hand-coins" />
                                                Proses di daftar billing
                                            </x-button>
                                        </div>
                                    </div>
                                </x-table.cell>
                            </x-table.row>
                        </x-table.body>
                    @endforeach
                </x-table>
            </x-card>
        @empty
            <x-card>
                <x-card.content class="py-12 text-center">
                    <div class="mx-auto mb-2 flex size-10 items-center justify-center rounded-full bg-muted">
                        <x-icon name="table-2" class="size-5 text-muted-foreground" />
                    </div>
                    <p class="text-sm font-medium">Tidak ada data terverifikasi</p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Belum ada dokumen terverifikasi yang cocok dengan filter.
                    </p>
                </x-card.content>
            </x-card>
        @endforelse

    </div>
</x-app-layout>
