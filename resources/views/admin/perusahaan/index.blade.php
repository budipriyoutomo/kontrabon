<x-app-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-slate-800">
            Master Data Perusahaan
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Toolbar -->
            <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">

                <form method="GET"
                      action="{{ route('admin.perusahaan.index') }}"
                      class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">

                    <div class="relative">
                        <i class="bi bi-search absolute left-3 top-2.5 text-slate-400"></i>
                        <input type="text" name="search"
                               value="{{ request('search') }}"
                               placeholder="Cari nama / kode / NPWP"
                               class="w-full sm:w-64 pl-9 pr-3 py-2 border rounded-md text-sm">
                    </div>

                    <select name="status" class="border rounded-md px-3 py-2 text-sm">
                        <option value="">Semua status</option>
                        <option value="aktif" @selected(request('status')=='aktif')>Aktif</option>
                        <option value="nonaktif" @selected(request('status')=='nonaktif')>Nonaktif</option>
                    </select>

                    <div class="flex gap-2">
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">
                            Terapkan
                        </button>

                        @if(request()->hasAny(['search','status']))
                            <a href="{{ route('admin.perusahaan.index') }}"
                               class="px-4 py-2 bg-slate-200 text-slate-700 rounded-md text-sm hover:bg-slate-300">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>

                <a href="{{ route('admin.perusahaan.create') }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2
                          bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                    <i class="bi bi-plus-lg"></i>
                    Tambah Perusahaan
                </a>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'kode', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center justify-between hover:text-slate-800">
                                        Kode
                                        @if(request('sort') == 'kode')
                                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @else
                                            <i class="bi bi-arrows-expand ml-1 text-slate-300"></i>
                                        @endif
                                    </a>
                                </th>

                                <th class="px-4 py-3 text-left font-medium">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'nama', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center justify-between hover:text-slate-800">
                                        Nama Perusahaan
                                        @if(request('sort') == 'nama')
                                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @else
                                            <i class="bi bi-arrows-expand ml-1 text-slate-300"></i>
                                        @endif
                                    </a>
                                </th>

                                <th class="px-4 py-3 text-left font-medium">NPWP</th>
                                <th class="px-4 py-3 text-left font-medium">TOP</th>
                                <th class="px-4 py-3 text-left font-medium">PIC</th>
                                <th class="px-4 py-3 text-left font-medium">Email</th>
                                <th class="px-4 py-3 text-center font-medium">Faktur</th>
                                <th class="px-4 py-3 text-left font-medium">Status</th>
                                <th class="px-4 py-3 text-right font-medium">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @forelse($data as $row)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                        {{ $row->kode ?: '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="font-medium text-slate-800">
                                            {{ $row->nama }}
                                        </div>
                                        @if($row->alamat)
                                            <div class="text-xs text-slate-500">{{ $row->alamat }}</div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                        {{ $row->npwp ?: '-' }}
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                        {{ $row->top !== null ? $row->top . ' hari' : '-' }}
                                    </td>

                                    <td class="px-4 py-3 text-slate-600">
                                        {{ $row->nama_pic ?: '-' }}
                                    </td>

                                    <td class="px-4 py-3 text-slate-600">
                                        {{ $row->email ?: '-' }}
                                    </td>

                                    <td class="px-4 py-3 text-center text-slate-600">
                                        {{ $row->tukar_fakturs_count }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-1 rounded-md text-xs font-medium
                                            @class([
                                                'bg-green-100 text-green-700' => $row->is_active,
                                                'bg-slate-100 text-slate-600' => ! $row->is_active,
                                            ])">
                                            {{ $row->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end space-x-2">
                                            <a href="{{ route('admin.perusahaan.show', $row->id) }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md
                                                      text-xs font-medium text-indigo-600 border border-indigo-200
                                                      hover:bg-indigo-50 hover:text-indigo-800 transition">
                                                <i class="bi bi-eye"></i>
                                                Detail
                                            </a>

                                            <a href="{{ route('admin.perusahaan.edit', $row->id) }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md
                                                      text-xs font-medium text-slate-600 border border-slate-200
                                                      hover:bg-slate-50 hover:text-slate-800 transition">
                                                <i class="bi bi-pencil"></i>
                                                Edit
                                            </a>

                                            @if($row->tukar_fakturs_count === 0)
                                                <button type="button"
                                                        onclick="confirmDelete('{{ route('admin.perusahaan.destroy', $row->id) }}')"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md
                                                               text-xs font-medium text-red-600 border border-red-200
                                                               hover:bg-red-50 hover:text-red-700 transition">
                                                    <i class="bi bi-trash"></i>
                                                    Hapus
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-slate-500">
                                        @if(request()->hasAny(['search','status']))
                                            Tidak ada data yang sesuai dengan filter
                                        @else
                                            Belum ada data perusahaan
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-slate-100">
                    {{ $data->appends(request()->query())->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

<script>
function confirmDelete(url) {
    Swal.fire({
        title: 'Yakin hapus perusahaan?',
        text: 'Data yang dihapus tidak bisa dikembalikan',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;

            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;

            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
