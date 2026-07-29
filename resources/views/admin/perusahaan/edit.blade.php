<x-app-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-slate-800">
            Edit Perusahaan
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <form method="POST"
                  action="{{ route('admin.perusahaan.update', $perusahaan->id) }}"
                  class="space-y-6">
                @csrf
                @method('PUT')

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-sm font-semibold text-slate-700 mb-4">
                        Data Perusahaan
                    </h3>

                    @include('admin.perusahaan._form', ['perusahaan' => $perusahaan])
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.perusahaan.index') }}"
                       class="px-4 py-2 border border-slate-300 rounded-md text-sm">
                        Batal
                    </a>

                    <button type="submit"
                            class="px-6 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                        Simpan Perubahan
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
