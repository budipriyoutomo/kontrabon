<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>
    <x-slot name="header">Dashboard</x-slot>

    <div class="space-y-6">

        <div class="space-y-1">
            <h1 class="text-xl font-semibold tracking-tight">Halo, {{ Auth::user()->name }}</h1>
            <p class="text-sm text-muted-foreground">
                Ringkasan pengajuan tukar faktur per hari ini.
            </p>
        </div>

        @if ($ringkasan === null)
            <x-alert icon="info">
                <x-alert.title>Belum ada ringkasan untuk peran Anda</x-alert.title>
                <x-alert.description>
                    Akun ini tidak memiliki akses ke data tukar faktur. Hubungi admin bila menurut Anda ini keliru.
                </x-alert.description>
            </x-alert>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-stat-card
                    label="Total Pengajuan"
                    icon="receipt-text"
                    :value="number_format($ringkasan['total'], 0, ',', '.')"
                    :hint="$ringkasan['pending'] . ' masih pending'"
                />

                <x-stat-card
                    label="Menunggu Verifikasi"
                    icon="clock"
                    :value="number_format($ringkasan['menungguVerifikasi'], 0, ',', '.')"
                    hint="Email sudah terkirim ke supplier"
                />

                <x-stat-card
                    label="Terverifikasi"
                    icon="badge-check"
                    :value="number_format($ringkasan['terverifikasi'], 0, ',', '.')"
                    hint="Siap diproses billing"
                />

                <x-stat-card
                    label="Masuk Billing"
                    icon="wallet"
                    :value="number_format($ringkasan['billing'], 0, ',', '.')"
                    hint="Sudah selesai diproses"
                />
            </div>

            <x-card class="overflow-hidden">
                <x-card.header class="flex-row items-center justify-between space-y-0">
                    <div class="space-y-1.5">
                        <x-card.title>Pengajuan Terbaru</x-card.title>
                        <x-card.description>Delapan pengajuan dengan tanggal tukar paling akhir.</x-card.description>
                    </div>

                    @can('viewAny', App\Models\TukarFaktur::class)
                        <x-button variant="outline" size="sm" :href="route('admin.tukar-faktur.index')">
                            Lihat semua
                            <x-icon name="chevron-right" />
                        </x-button>
                    @endcan
                </x-card.header>

                <x-table>
                    <x-table.header>
                        <x-table.row>
                            <x-table.head>Tgl Tukar</x-table.head>
                            <x-table.head>Supplier</x-table.head>
                            <x-table.head>PT Tujuan</x-table.head>
                            <x-table.head class="text-right">Jumlah</x-table.head>
                            <x-table.head>Status</x-table.head>
                        </x-table.row>
                    </x-table.header>

                    <x-table.body>
                        @forelse ($terbaru as $row)
                            <x-table.row>
                                <x-table.cell class="whitespace-nowrap text-muted-foreground">
                                    {{ \Carbon\Carbon::parse($row->tanggal_tukar)->format('d M Y') }}
                                </x-table.cell>

                                <x-table.cell class="font-medium">{{ $row->perusahaan_pengaju }}</x-table.cell>

                                <x-table.cell class="text-muted-foreground">{{ $row->pt_tujuan }}</x-table.cell>

                                <x-table.cell class="whitespace-nowrap text-right tabular-nums">
                                    Rp {{ number_format($row->jumlah_rupiah, 0, ',', '.') }}
                                </x-table.cell>

                                <x-table.cell>
                                    <x-badge :status="$row->status" />
                                </x-table.cell>
                            </x-table.row>
                        @empty
                            <x-table.empty
                                :colspan="5"
                                icon="receipt-text"
                                title="Belum ada pengajuan"
                                description="Pengajuan tukar faktur dari supplier akan muncul di sini."
                            />
                        @endforelse
                    </x-table.body>
                </x-table>
            </x-card>
        @endif

    </div>
</x-app-layout>
