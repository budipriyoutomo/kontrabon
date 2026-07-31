@php
    $perusahaan = $perusahaan ?? null;
@endphp

@if ($errors->any())
    <x-alert variant="destructive" icon="triangle-alert" class="mb-6">
        <x-alert.title>Data belum tersimpan</x-alert.title>
        <x-alert.description>
            <ul class="mt-1 list-disc space-y-1 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert.description>
    </x-alert>
@endif

<div class="grid gap-4 sm:grid-cols-2">

    <x-form-field label="Kode Perusahaan" name="kode">
        <x-input
            type="text"
            name="kode"
            :value="old('kode', $perusahaan->kode ?? '')"
            placeholder="Opsional, contoh: VND-001"
        />
    </x-form-field>

    <x-form-field label="Nama Perusahaan" name="nama" required>
        <x-input
            type="text"
            name="nama"
            :value="old('nama', $perusahaan->nama ?? '')"
            placeholder="Contoh: PT Vendor Jaya"
            required
        />
    </x-form-field>

    <x-form-field label="NPWP" name="npwp">
        <x-input type="text" name="npwp" :value="old('npwp', $perusahaan->npwp ?? '')" />
    </x-form-field>

    <x-form-field label="TOP" name="top" hint="Term of payment, dalam hari.">
        <x-input
            type="number"
            name="top"
            min="0"
            max="365"
            step="1"
            :value="old('top', $perusahaan->top ?? '')"
            placeholder="Contoh: 30"
        />
    </x-form-field>

    <x-form-field label="Telepon" name="telepon">
        <x-input type="text" name="telepon" :value="old('telepon', $perusahaan->telepon ?? '')" />
    </x-form-field>

    <x-form-field label="Alamat" name="alamat" class="sm:col-span-2">
        <x-input type="text" name="alamat" :value="old('alamat', $perusahaan->alamat ?? '')" />
    </x-form-field>

    <x-form-field label="Nama PIC" name="nama_pic">
        <x-input type="text" name="nama_pic" :value="old('nama_pic', $perusahaan->nama_pic ?? '')" />
    </x-form-field>

    <x-form-field label="Email" name="email">
        <x-input
            type="email"
            name="email"
            :value="old('email', $perusahaan->email ?? '')"
            placeholder="finance@perusahaan.com"
        />
    </x-form-field>

    <div class="sm:col-span-2">
        <label class="flex items-center gap-2 text-sm">
            {{-- Input tersembunyi menjaga field tetap terkirim saat kotak tidak dicentang. --}}
            <input type="hidden" name="is_active" value="0">

            <x-checkbox name="is_active" value="1" @checked(old('is_active', $perusahaan->is_active ?? true)) />
            Perusahaan aktif (tampil di form pengajuan tukar faktur)
        </label>
    </div>

</div>
