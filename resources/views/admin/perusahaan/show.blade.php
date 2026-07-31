<x-app-layout>
    <x-slot name="title">{{ $perusahaan->nama }}</x-slot>

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Master Perusahaan', 'url' => route('admin.perusahaan.index')],
            ['label' => $perusahaan->nama],
        ]" />
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6">

        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="space-y-1">
                <h1 class="text-xl font-semibold tracking-tight">{{ $perusahaan->nama }}</h1>
                <p class="text-sm text-muted-foreground">Detail master data perusahaan</p>
            </div>

            <x-button :href="route('admin.perusahaan.edit', $perusahaan->id)">
                <x-icon name="pencil" />
                Edit
            </x-button>
        </div>

        <x-card>
            <x-card.content class="space-y-6 pt-6">
                <div class="flex flex-wrap items-center justify-between gap-6">
                    <div class="space-y-1">
                        <p class="text-sm text-muted-foreground">Total Tukar Faktur</p>
                        <p class="text-2xl font-semibold tabular-nums tracking-tight">
                            {{ $perusahaan->tukar_fakturs_count }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-sm text-muted-foreground">Status</p>
                        <x-badge :variant="$perusahaan->is_active ? 'success' : 'muted'" class="px-3 py-1 text-sm">
                            {{ $perusahaan->is_active ? 'Aktif' : 'Nonaktif' }}
                        </x-badge>
                    </div>
                </div>

                <dl class="grid gap-x-6 border-t pt-6 sm:grid-cols-2">
                    @foreach ([
                        'Kode Perusahaan' => $perusahaan->kode,
                        'Nama Perusahaan' => $perusahaan->nama,
                        'NPWP' => $perusahaan->npwp,
                        'TOP' => $perusahaan->top !== null ? $perusahaan->top . ' hari' : null,
                        'Telepon' => $perusahaan->telepon,
                        'Alamat' => $perusahaan->alamat,
                        'Nama PIC' => $perusahaan->nama_pic,
                        'Email' => $perusahaan->email,
                    ] as $label => $value)
                        <div class="space-y-1 py-2">
                            <dt class="text-xs text-muted-foreground">{{ $label }}</dt>
                            <dd class="text-sm font-medium">{{ $value ?: '-' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-card.content>
        </x-card>

        <x-card class="overflow-hidden">
            <x-card.header>
                <x-card.title>Riwayat Tukar Faktur</x-card.title>
            </x-card.header>

            <x-table>
                <x-table.header>
                    <x-table.row>
                        <x-table.head>Tgl Tukar</x-table.head>
                        <x-table.head>PT Tujuan</x-table.head>
                        <x-table.head>No Kwitansi</x-table.head>
                        <x-table.head class="text-right">Jumlah</x-table.head>
                        <x-table.head>Status</x-table.head>
                        <x-table.head class="text-right">Aksi</x-table.head>
                    </x-table.row>
                </x-table.header>

                <x-table.body>
                    @forelse ($tukarFakturs as $row)
                        <x-table.row>
                            <x-table.cell class="whitespace-nowrap text-muted-foreground">
                                {{ \Carbon\Carbon::parse($row->tanggal_tukar)->format('d M Y') }}
                            </x-table.cell>

                            <x-table.cell>{{ $row->pt_tujuan }}</x-table.cell>

                            <x-table.cell class="font-mono text-xs">{{ $row->no_kwitansi }}</x-table.cell>

                            <x-table.cell class="whitespace-nowrap text-right tabular-nums">
                                Rp {{ number_format($row->jumlah_rupiah, 0, ',', '.') }}
                            </x-table.cell>

                            <x-table.cell>
                                <x-badge :status="$row->status" />
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
                            :colspan="6"
                            icon="receipt-text"
                            title="Belum ada tukar faktur"
                            description="Perusahaan ini belum pernah mengajukan tukar faktur."
                        />
                    @endforelse
                </x-table.body>
            </x-table>

            @if ($tukarFakturs->hasPages())
                <div class="border-t px-4 py-3">
                    {{ $tukarFakturs->links() }}
                </div>
            @endif
        </x-card>

        <div>
            <x-button variant="outline" :href="route('admin.perusahaan.index')">
                <x-icon name="arrow-left" />
                Kembali ke daftar perusahaan
            </x-button>
        </div>

    </div>
</x-app-layout>
