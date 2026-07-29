<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">
                    Detail Tukar Faktur
                </h2>
                <p class="text-sm text-slate-500">
                    Informasi lengkap pengajuan tukar faktur
                </p>
            </div>

            @if($data->status !== 'approved')
                <button 
                    x-data
                    @click="$dispatch('edit-mode')"
                    class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                    ✏️ Edit
                </button>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <div 
                x-data="{ editing: false }"
                x-on:edit-mode.window="editing = true"
            >

                <form method="POST"
                    action="{{ route('admin.tukar-faktur.update', $data->id) }}"
                    class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- SUMMARY -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                        <div class="flex justify-between items-center mb-6">

                            <div>
                                <div class="text-sm text-slate-500">Jumlah</div>

                                <!-- View -->
                                <div x-show="!editing"
                                    class="text-2xl font-semibold text-slate-800">
                                    Rp {{ number_format($data->jumlah_rupiah, 0, ',', '.') }}
                                </div>

                                <!-- Edit -->
                                <input x-show="editing"
                                    type="number"
                                    name="jumlah_rupiah"
                                    value="{{ $data->jumlah_rupiah }}"
                                    class="mt-1 rounded-md border-slate-300 text-sm">
                            </div>

                            <div>
                                <div class="text-sm text-slate-500">Status</div>

                                <!-- View -->
                                <span x-show="!editing"
                                    class="inline-flex px-3 py-1 rounded-full text-sm font-medium
                                        @class([
                                            'bg-green-100 text-green-700' => $data->status === 'approved',
                                            'bg-red-100 text-red-700' => $data->status === 'rejected',
                                            'bg-slate-100 text-slate-700' => $data->status === 'submitted',
                                        ])">
                                    {{ ucfirst($data->status) }}
                                </span>

                                <!-- Edit -->
                                <select x-show="editing"
                                    name="status"
                                    class="rounded-md border-slate-300 text-sm">
                                    <option value="submitted">Submitted</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- INFORMASI FAKTUR -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                        <h3 class="text-sm font-semibold text-slate-700 mb-4">
                            Informasi Faktur
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                            @foreach([
                                'pt_tujuan' => 'PT Tujuan',
                                'perusahaan_pengaju' => 'Perusahaan Pengaju',
                                'no_kwitansi' => 'No Kwitansi',
                                'nama_pic' => 'Nama PIC',
                                'email_penerima' => 'Email PIC',
                            ] as $field => $label)

                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">
                                        {{ $label }}
                                    </label>

                                    <!-- View -->
                                    <div x-show="!editing"
                                        class="text-sm text-slate-800 font-medium">
                                        {{ $data->$field }}
                                    </div>

                                    <!-- Edit -->
                                    <input x-show="editing"
                                        type="text"
                                        name="{{ $field }}"
                                        value="{{ $data->$field }}"
                                        class="w-full rounded-md border-slate-300 text-sm">
                                </div>

                            @endforeach

                        </div>
                    </div>

                    <!-- ACTION -->
                    <div x-show="editing"
                        class="flex justify-end gap-3">

                        <button type="button"
                            @click="editing = false"
                            class="px-4 py-2 border border-slate-300 rounded-md text-sm">
                            Batal
                        </button>

                        <button type="submit"
                            class="px-6 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                            💾 Simpan Perubahan
                        </button>

                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>
