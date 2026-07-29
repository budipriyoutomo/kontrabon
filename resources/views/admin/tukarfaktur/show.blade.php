 
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">
                Detail Tukar Faktur
            </h2>
            <p class="text-sm text-slate-500">
                Informasi lengkap pengajuan tukar faktur
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- SUMMARY -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">

                    <div>
                        <div class="text-sm text-slate-500">Jumlah</div>
                        <div class="text-2xl font-semibold text-slate-800">
                            Rp {{ number_format($data->jumlah_rupiah, 0, ',', '.') }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-slate-500">Status</div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @class([
                                'bg-green-100 text-green-700' => $data->status === 'email_sent', 
                                'bg-slate-100 text-slate-700' => $data->status === 'pending',
                            ])">
                            {{ ucfirst($data->status) }}
                        </span>
                    </div>

                </div>
            </div>
            <!-- INFORMASI FAKTUR -->
<div 
    x-data="{ openEdit: false }"
    class="bg-white rounded-xl shadow-sm border border-slate-200 p-6"
>

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-sm font-semibold text-slate-700">
            Informasi Faktur
        </h3>

        @if($data->status !== 'approved')
            <button
                @click="openEdit = true"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-md
                       bg-indigo-600 text-white text-sm font-medium
                       hover:bg-indigo-700 transition">
                ✏️ Edit Data
            </button>
        @endif
    </div>

    <!-- BODY -->
    <div class="px-6 py-5 text-sm">
        <div class="grid grid-cols-1 gap-y-4">

            @foreach([
                'pt_tujuan' => 'PT Tujuan',
                'perusahaan_pengaju' => 'Perusahaan Pengaju',
                'tanggal_tukar' => 'Tanggal Tukar',
                'no_kwitansi' => 'No Kwitansi',
                'jumlah_rupiah' => 'Jumlah Rupiah',
                'nama_pic' => 'Nama PIC',
                'email_penerima' => 'Email PIC',
            ] as $field => $label)

                <div class="grid grid-cols-[180px_1fr] gap-x-6 py-2">
                    <div class="text-slate-500">
                        {{ $label }}
                    </div>
                    <div class="font-medium text-slate-800">
                        @if($field === 'tanggal_tukar')
                            {{ \Carbon\Carbon::parse($data->$field)->format('d F Y') }}
                        @elseif($field === 'jumlah_rupiah')
                            Rp {{ number_format($data->$field, 0, ',', '.') }}
                        @else
                            {{ $data->$field }}
                        @endif
                    </div>
                </div>

            @endforeach

        </div>
    </div>

    <!-- MODAL TAILWIND --> 
<div 
    x-show="openEdit"
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center"
    style="display: none;"
>

    <!-- Backdrop -->
    <div 
        class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
        @click="openEdit = false">
    </div>

    <!-- Modal Box -->
    <div 
        x-transition.scale
        class="relative bg-white w-full max-w-2xl rounded-2xl shadow-2xl 
               border border-slate-200
               max-h-[85vh] overflow-y-auto"
    >

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b bg-slate-50 rounded-t-2xl">
            <div>
                <h3 class="text-lg font-semibold text-slate-800">
                    Edit Tukar Faktur
                </h3>
                <p class="text-xs text-slate-500">
                    Perbarui informasi faktur
                </p>
            </div>

            <button 
                @click="openEdit = false"
                class="text-slate-400 hover:text-slate-600 text-xl transition">
                ×
            </button>
        </div>

        <!-- Body -->
        <form method="POST"
              action="{{ route('admin.tukar-faktur.update', $data->id) }}"
              class="px-6 py-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        PT Tujuan
                    </label>
                    <input type="text" name="pt_tujuan"
                        value="{{ $data->pt_tujuan }}"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Perusahaan Pengaju
                    </label>
                    <input type="text" name="perusahaan_pengaju"
                        value="{{ $data->perusahaan_pengaju }}"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Tanggal Tukar
                    </label>
                    <input type="date" name="tanggal_tukar"
                        value="{{ $data->tanggal_tukar }}"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        No Kwitansi
                    </label>
                    <input type="text" name="no_kwitansi"
                        value="{{ $data->no_kwitansi }}"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Jumlah Rupiah
                    </label>
                    <input type="number" name="jumlah_rupiah"
                        value="{{ $data->jumlah_rupiah }}"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Nama PIC
                    </label>
                    <input type="text" name="nama_pic"
                        value="{{ $data->nama_pic }}"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Email Penerima
                    </label>
                    <input type="email" name="email_penerima"
                        value="{{ $data->email_penerima }}"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 pt-4 border-t mt-6">
                <button type="button"
                        @click="openEdit = false"
                        class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm hover:bg-slate-200 transition">
                    Batal
                </button>

                <button type="submit"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Simpan Perubahan
                </button>
            </div>

        </form>

    </div>
</div>



</div>


            <!-- PAYMENT -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-sm font-semibold text-slate-700 mb-4">
                        Informasi Pembayaran
                    </h3>

                    <form method="POST"
                        action="{{ route('admin.tukar-faktur.payment-date', $data->id) }}"
                        class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-end">
                        @csrf

                        <div>
                            <label class="block text-xs text-slate-500 mb-1">
                                Tanggal Pembayaran
                            </label>

                            <input type="date"
                                name="tanggal_pembayaran"
                                value="{{ $data->tanggal_pembayaran }}"
                                class="w-full rounded-md border-slate-300 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                required>
                        </div>

                        <div>
                            <button
                                class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 text-white text-sm hover:bg-indigo-700">
                                Simpan Pembayaran
                            </button>
                        </div>
                    </form>

                    @if($data->tanggal_pembayaran)
                        <p class="text-xs text-slate-500 mt-3">
                            Terakhir diperbarui:
                            {{ \Carbon\Carbon::parse($data->tanggal_pembayaran)->format('d M Y') }}
                        </p>
                    @endif
                </div>

                <!-- ACTIONS -->
                <div class="flex items-center justify-between">

                    <!-- KIRI: Kembali -->
                    <a href="{{ route('admin.tukar-faktur.index') }}"
                    class="inline-flex items-center px-4 py-2 rounded-md
                            border border-slate-300 text-sm text-slate-700
                            hover:bg-slate-50">
                        ← Kembali
                    </a>

                    <!-- KANAN: Submit / Simpan 
                    <form method="POST" action="{{ route('admin.tukar-faktur.payment-date', $data->id) }}">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center px-5 py-2 rounded-md
                                bg-slate-800 text-white text-sm font-medium
                                hover:bg-slate-900
                                focus:outline-none focus:ring-2 focus:ring-slate-400">
                            Simpan
                        </button>
                    </form>-->

                </div>



        </div>
    </div>
</x-app-layout>
