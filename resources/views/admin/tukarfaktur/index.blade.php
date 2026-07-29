<x-app-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-slate-800">
            Data Tukar Faktur
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Filter Section --> 
            <div
                x-data="{
                    open: {{ request()->hasAny(['search','start_date','end_date','start_bayar','end_bayar','pt_tujuan','status','perusahaan']) ? 'true' : 'false' }}
                }"
                class="mb-4"
            >

                <!-- Toggle Button -->
                <div class="flex justify-between items-center mb-3">
                    <button
                        type="button"
                        @click="open = !open"
                        class="inline-flex items-center gap-2
                            px-4 py-2 rounded-md
                            bg-indigo-600 text-white text-sm font-medium
                            hover:bg-indigo-700 transition"
                    >
                        <i class="bi bi-funnel"></i>
                        Filter Data
                        <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                    </button>

                    <div class="flex items-center gap-3">
                        @if(request()->hasAny(['search','start_date','end_date','pt_tujuan','status','perusahaan']))
                            <span class="text-xs text-slate-500 italic">
                                Filter aktif
                            </span>
                        @endif

                        <a href="{{ route('admin.tukar-faktur.export', request()->except('page')) }}"
                           class="inline-flex items-center gap-2
                                  px-4 py-2 rounded-md
                                  bg-emerald-600 text-white text-sm font-medium
                                  hover:bg-emerald-700 transition">
                            <i class="bi bi-file-earmark-spreadsheet"></i>
                            Export Excel
                        </a>
                    </div>
                </div>

                <!-- Filter Card -->
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2"
                    class="bg-white rounded-lg shadow-sm border"
                >
                    <form
                        method="GET"
                        action="{{ route('admin.tukar-faktur.index') }}"
                        class="p-4 space-y-4"
                    >

                        <!-- GRID FILTER -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 m-2">
                             <!-- Tanggal -->
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">
                                    Tanggal Tukar
                                </label>
                                <div class="flex gap-2">
                                    <input type="date" name="start_date"
                                        value="{{ request('start_date') }}"
                                        class="w-full border rounded-md px-3 py-2 text-sm">
                                     
                                    <input type="date" name="end_date"
                                        value="{{ request('end_date') }}"
                                        class="w-full border rounded-md px-3 py-2 text-sm">
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 m-2">
                            <!-- Tanggal Bayar -->
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">
                                    Tanggal Bayar
                                </label>
                                <div class="flex gap-2">
                                    <input type="date" name="start_bayar"
                                        value="{{ request('start_bayar') }}"
                                        class="w-full border rounded-md px-3 py-2 text-sm">

                                    <input type="date" name="end_bayar"
                                        value="{{ request('end_bayar') }}"
                                        class="w-full border rounded-md px-3 py-2 text-sm">
                                </div>
                            </div>
                        </div>   
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 m-2">  
                            <!-- PT Tujuan -->
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">
                                    PT Tujuan
                                </label>
                                <select name="pt_tujuan"
                                    class="w-full border rounded-md px-3 py-2 text-sm">
                                    <option value="">Semua</option>
                                    @foreach($ptTujuanOptions as $option)
                                        <option value="{{ $option }}" @selected(request('pt_tujuan')==$option)>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">
                                    Status
                                </label>
                                <select name="status"
                                    class="w-full border rounded-md px-3 py-2 text-sm">
                                    <option value="">Semua</option>
                                    <option value="pending" @selected(request('status')=='pending')>Pending</option> 
                                    <option value="email_sent" @selected(request('status')=='email_sent')>Email Sent</option>
                                </select>
                            </div>

                            <!-- Perusahaan -->
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">
                                    Perusahaan
                                </label>
                                <select name="perusahaan"
                                    class="w-full border rounded-md px-3 py-2 text-sm">
                                    <option value="">Semua</option>
                                    @foreach($perusahaanOptions as $option)
                                        <option value="{{ $option }}" @selected(request('perusahaan')==$option)>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- SEARCH + ACTION --> 
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 pt-3 border-t">

                            <!-- Search -->
                            <div class="relative w-full md:w-1/3">
                                <i class="bi bi-search absolute left-3 top-2.5 text-slate-400"></i>
                                <input type="text" name="search"
                                    value="{{ request('search') }}"
                                    placeholder="Cari No Kwitansi"
                                    class="w-full pl-9 pr-3 py-2 border rounded-md text-sm">
                            </div>

                            <!-- Buttons -->
                            <div class="flex gap-2">
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700"
                                >
                                    Terapkan
                                </button>

                                <a
                                    href="{{ route('admin.tukar-faktur.index') }}"
                                    class="px-4 py-2 bg-slate-200 text-slate-700 rounded-md text-sm hover:bg-slate-300"
                                    @click="open = false"
                                >
                                    Reset
                                </a>
                            </div>
                        </div>

                    </form>
                </div>
            </div> 


            <!-- Table Section -->
            <div class="bg-white rounded-lg shadow-sm">

                <!-- Table Wrapper -->
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <!-- Sorting headers -->
                                <th class="px-4 py-3 text-left font-medium">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'tanggal_tukar', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center justify-between hover:text-slate-800">
                                        Tgl Tukar
                                        @if(request('sort') == 'tanggal_tukar')
                                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @else
                                            <i class="bi bi-arrows-expand ml-1 text-slate-300"></i>
                                        @endif
                                    </a>
                                </th>
                                
                                <th class="px-4 py-3 text-left font-medium">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'pt_tujuan', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center justify-between hover:text-slate-800">
                                        PT Tujuan
                                        @if(request('sort') == 'pt_tujuan')
                                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @else
                                            <i class="bi bi-arrows-expand ml-1 text-slate-300"></i>
                                        @endif
                                    </a>
                                </th>
                                
                                <th class="px-4 py-3 text-left font-medium">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'perusahaan_pengaju', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center justify-between hover:text-slate-800">
                                        Perusahaan
                                        @if(request('sort') == 'perusahaan_pengaju')
                                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @else
                                            <i class="bi bi-arrows-expand ml-1 text-slate-300"></i>
                                        @endif
                                    </a>
                                </th>

                                <th class="px-4 py-3 text-left font-medium">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'no_kwitansi', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center justify-between hover:text-slate-800">
                                        No Kwitansi
                                        @if(request('sort') == 'no_kwitansi')
                                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @else
                                            <i class="bi bi-arrows-expand ml-1 text-slate-300"></i>
                                        @endif
                                    </a>
                                </th>
                                
                                <th class="px-4 py-3 text-left font-medium">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'jumlah_rupiah', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center justify-between hover:text-slate-800">
                                        Jumlah
                                        @if(request('sort') == 'jumlah_rupiah')
                                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @else
                                            <i class="bi bi-arrows-expand ml-1 text-slate-300"></i>
                                        @endif
                                    </a>
                                </th>
                                
                                <th class="px-4 py-3 text-left font-medium">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center justify-between hover:text-slate-800">
                                        Status
                                        @if(request('sort') == 'status')
                                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @else
                                            <i class="bi bi-arrows-expand ml-1 text-slate-300"></i>
                                        @endif
                                    </a>
                                </th>
                                
                                <th class="px-4 py-3 text-left font-medium">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'tanggal_pembayaran', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center justify-between hover:text-slate-800">
                                        Tgl Bayar
                                        @if(request('sort') == 'tanggal_pembayaran')
                                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @else
                                            <i class="bi bi-arrows-expand ml-1 text-slate-300"></i>
                                        @endif
                                    </a>
                                </th>
                                
                                <th class="px-4 py-3 text-right font-medium">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @forelse($data as $row)
                            <tr class="hover:bg-slate-50 transition">
                                <!-- Tanggal Tukar -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($row->tanggal_tukar)->format('d M Y') }}
                                </td>
                                
                                <!-- PT Tujuan -->
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-800">
                                        {{ $row->pt_tujuan }}
                                    </div>
                                </td>

                                <!-- Perusahaan -->
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-800">
                                        {{ $row->perusahaan_pengaju }}
                                    </div>
                                </td>
                                 <!-- Perusahaan -->
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-800">
                                        {{ $row->no_kwitansi }}
                                    </div>
                                </td>

                                <!-- Jumlah -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    Rp {{ number_format($row->jumlah_rupiah, 0, ',', '.') }}
                                </td>

                                <!-- Status -->
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-1 rounded-md text-xs font-medium
                                        @class([
                                            'bg-green-100 text-green-700' => $row->status === 'approved',
                                            'bg-red-100 text-red-700' => $row->status === 'rejected',
                                            'bg-slate-100 text-slate-700' => $row->status === 'submitted',
                                            'bg-blue-100 text-blue-700' => $row->status === 'email_sent',
                                        ])">
                                        {{ ucfirst($row->status) }}
                                    </span>
                                </td>

                                <!-- Tanggal Pembayaran -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($row->tanggal_pembayaran)
                                        <span class="text-slate-700">
                                            {{ \Carbon\Carbon::parse($row->tanggal_pembayaran)->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic text-xs">
                                            Belum dibayar
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end space-x-2">
                                        <a href="{{ route('admin.tukar-faktur.show', $row->id) }}"
                                            class="inline-flex items-center gap-1.5
                                            px-3 py-1.5 rounded-md
                                            text-xs font-medium
                                            text-indigo-600 border border-indigo-200
                                            hover:bg-indigo-50 hover:text-indigo-800
                                            transition">
                                            <i class="bi bi-eye"></i>
                                            Detail
                                        </a>

                                        @if($row->status !== 'email_sent')
                                            <!-- Delete -->
                                            <button
                                                type="button"
                                                onclick="confirmDelete('{{ route('admin.tukar-faktur.destroy', $row->id) }}')"
                                                class="inline-flex items-center gap-1.5
                                                px-3 py-1.5 rounded-md
                                                text-xs font-medium
                                                text-red-600 border border-red-200
                                                hover:bg-red-50 hover:text-red-700
                                                transition">
                                                <i class="bi bi-trash"></i>
                                                Hapus 
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                    @if(request()->hasAny(['search', 'start_date', 'end_date', 'pt_tujuan', 'status', 'perusahaan']))
                                        Tidak ada data yang sesuai dengan filter
                                    @else
                                        Tidak ada data
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-4 py-3 border-t border-slate-100">
                    {{ $data->appends(request()->query())->links() }}
                </div>

            </div>

        </div>
    </div> 
</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmDelete(url) {
    Swal.fire({
        title: 'Yakin hapus data?',
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