<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">
                    {{ $perusahaan->nama }}
                </h2>
                <p class="text-sm text-slate-500">
                    Detail master data perusahaan
                </p>
            </div>

            <a href="{{ route('admin.perusahaan.edit', $perusahaan->id) }}"
               class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                Edit
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- INFORMASI -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <div class="text-sm text-slate-500">Total Tukar Faktur</div>
                        <div class="text-2xl font-semibold text-slate-800">
                            {{ $perusahaan->tukar_fakturs_count }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-slate-500">Status</div>
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium
                            @class([
                                'bg-green-100 text-green-700' => $perusahaan->is_active,
                                'bg-slate-100 text-slate-600' => ! $perusahaan->is_active,
                            ])">
                            {{ $perusahaan->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-6 border-t border-slate-100">
                    @foreach([
                        'Kode Perusahaan' => $perusahaan->kode,
                        'Nama Perusahaan' => $perusahaan->nama,
                        'NPWP'            => $perusahaan->npwp,
                        'TOP'             => $perusahaan->top !== null ? $perusahaan->top . ' hari' : null,
                        'Telepon'         => $perusahaan->telepon,
                        'Alamat'          => $perusahaan->alamat,
                        'Nama PIC'        => $perusahaan->nama_pic,
                        'Email'           => $perusahaan->email,
                    ] as $label => $value)
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">{{ $label }}</label>
                            <div class="text-sm text-slate-800 font-medium">
                                {{ $value ?: '-' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- RIWAYAT TUKAR FAKTUR -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                <div class="p-6 pb-4">
                    <h3 class="text-sm font-semibold text-slate-700">
                        Riwayat Tukar Faktur
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Tgl Tukar</th>
                                <th class="px-4 py-3 text-left font-medium">PT Tujuan</th>
                                <th class="px-4 py-3 text-left font-medium">No Kwitansi</th>
                                <th class="px-4 py-3 text-left font-medium">Jumlah</th>
                                <th class="px-4 py-3 text-left font-medium">Status</th>
                                <th class="px-4 py-3 text-right font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($tukarFakturs as $row)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($row->tanggal_tukar)->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3">{{ $row->pt_tujuan }}</td>
                                    <td class="px-4 py-3">{{ $row->no_kwitansi }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        Rp {{ number_format($row->jumlah_rupiah, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                            {{ ucfirst($row->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('admin.tukar-faktur.show', $row->id) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md
                                                  text-xs font-medium text-indigo-600 border border-indigo-200
                                                  hover:bg-indigo-50 hover:text-indigo-800 transition">
                                            <i class="bi bi-eye"></i>
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                        Belum ada tukar faktur dari perusahaan ini
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-slate-100">
                    {{ $tukarFakturs->links() }}
                </div>
            </div>

            <div>
                <a href="{{ route('admin.perusahaan.index') }}"
                   class="text-sm text-slate-500 hover:text-slate-800">
                    &larr; Kembali ke daftar perusahaan
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
