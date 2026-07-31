{{-- Panel ringkasan nominal, dihitung dari filter yang sedang aktif. --}}
<div class="grid gap-4 lg:grid-cols-3">

    <x-stat-card
        label="Total Tagihan"
        icon="banknote"
        :value="'Rp ' . number_format($ringkasan['totalRupiah'], 0, ',', '.')"
        :hint="$ringkasan['jumlahDokumen'] . ' dokumen'"
    />

    <x-stat-card label="Per PT Tujuan" icon="building-2">
        <div class="divide-y">
            @forelse ($ringkasan['perPt'] as $baris)
                <div class="flex justify-between gap-3 py-1.5 text-sm">
                    <span class="truncate text-muted-foreground">{{ $baris->pt_tujuan }}</span>
                    <span class="whitespace-nowrap font-medium tabular-nums">
                        Rp {{ number_format($baris->total_rupiah, 0, ',', '.') }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-muted-foreground">Tidak ada data</p>
            @endforelse
        </div>
    </x-stat-card>

    <x-stat-card label="Per Tanggal Bayar" icon="calendar">
        <div class="max-h-40 divide-y overflow-y-auto">
            @forelse ($ringkasan['perTanggal'] as $baris)
                <div class="flex justify-between gap-3 py-1.5 text-sm">
                    <span class="whitespace-nowrap text-muted-foreground">
                        {{ $baris->tanggal_pembayaran
                            ? \Carbon\Carbon::parse($baris->tanggal_pembayaran)->format('d/m/Y')
                            : 'Belum ada tanggal' }}
                    </span>
                    <span class="whitespace-nowrap font-medium tabular-nums">
                        Rp {{ number_format($baris->total_rupiah, 0, ',', '.') }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-muted-foreground">Tidak ada data</p>
            @endforelse
        </div>
    </x-stat-card>

</div>
