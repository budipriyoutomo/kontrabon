@php
    $perusahaan = $perusahaan ?? null;
@endphp

@if($errors->any())
    <div class="mb-6 rounded-md border border-red-200 bg-red-50 p-4">
        <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

    <div>
        <label class="block text-xs text-slate-500 mb-1">Kode Perusahaan</label>
        <input type="text" name="kode"
               value="{{ old('kode', $perusahaan->kode ?? '') }}"
               placeholder="Opsional, contoh: VND-001"
               class="w-full rounded-md border-slate-300 text-sm">
    </div>

    <div>
        <label class="block text-xs text-slate-500 mb-1">
            Nama Perusahaan <span class="text-red-500">*</span>
        </label>
        <input type="text" name="nama"
               value="{{ old('nama', $perusahaan->nama ?? '') }}"
               placeholder="Contoh: PT Vendor Jaya"
               required
               class="w-full rounded-md border-slate-300 text-sm">
    </div>

    <div>
        <label class="block text-xs text-slate-500 mb-1">NPWP</label>
        <input type="text" name="npwp"
               value="{{ old('npwp', $perusahaan->npwp ?? '') }}"
               class="w-full rounded-md border-slate-300 text-sm">
    </div>

    <div>
        <label class="block text-xs text-slate-500 mb-1">
            TOP <span class="text-slate-400">(term of payment, hari)</span>
        </label>
        <input type="number" name="top" min="0" max="365" step="1"
               value="{{ old('top', $perusahaan->top ?? '') }}"
               placeholder="Contoh: 30"
               class="w-full rounded-md border-slate-300 text-sm">
    </div>

    <div>
        <label class="block text-xs text-slate-500 mb-1">Telepon</label>
        <input type="text" name="telepon"
               value="{{ old('telepon', $perusahaan->telepon ?? '') }}"
               class="w-full rounded-md border-slate-300 text-sm">
    </div>

    <div class="sm:col-span-2">
        <label class="block text-xs text-slate-500 mb-1">Alamat</label>
        <input type="text" name="alamat"
               value="{{ old('alamat', $perusahaan->alamat ?? '') }}"
               class="w-full rounded-md border-slate-300 text-sm">
    </div>

    <div>
        <label class="block text-xs text-slate-500 mb-1">Nama PIC</label>
        <input type="text" name="nama_pic"
               value="{{ old('nama_pic', $perusahaan->nama_pic ?? '') }}"
               class="w-full rounded-md border-slate-300 text-sm">
    </div>

    <div>
        <label class="block text-xs text-slate-500 mb-1">Email</label>
        <input type="email" name="email"
               value="{{ old('email', $perusahaan->email ?? '') }}"
               placeholder="finance@perusahaan.com"
               class="w-full rounded-md border-slate-300 text-sm">
    </div>

    <div class="sm:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1"
                   @checked(old('is_active', $perusahaan->is_active ?? true))
                   class="rounded border-slate-300 text-indigo-600">
            Perusahaan aktif (tampil di form pengajuan tukar faktur)
        </label>
    </div>

</div>
