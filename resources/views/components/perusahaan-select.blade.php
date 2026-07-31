@props([
    // Nama field yang dikirim. Form pengajuan memakai 'perusahaan_id',
    // filter admin memakai 'perusahaan' (berisi nama, bukan id).
    'name' => 'perusahaan_id',
    'value' => null,
    'label' => null,
    'placeholder' => 'Ketik nama supplier…',
    'required' => false,
    // Teks yang sudah terpilih, supaya opsi awal tetap tampil tanpa
    // menunggu hasil pencarian.
    'selectedLabel' => null,
    // Opsi tetap (dipakai filter admin yang juga harus memuat nama lama
    // dari data sebelum master perusahaan ada).
    'options' => null,
    // Selector target auto-isi, hanya berlaku untuk pengguna yang login.
    'targetPic' => null,
    'targetEmail' => null,
    'targetTop' => null,
    'ringkas' => false,
])

@php
    $id = $attributes->get('id') ?? 'perusahaan-select-' . str()->random(6);
@endphp

<div class="{{ $ringkas ? 'ps-ringkas' : '' }}">
    @if($label)
        <x-label :for="$id" :required="$required" class="mb-1.5">{{ $label }}</x-label>
    @endif

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        data-perusahaan-select
        data-endpoint="{{ route('perusahaan.search') }}"
        data-min-chars="2"
        placeholder="{{ $placeholder }}"
        @if($targetPic) data-target-pic="{{ $targetPic }}" @endif
        @if($targetEmail) data-target-email="{{ $targetEmail }}" @endif
        @if($targetTop) data-target-top="{{ $targetTop }}" @endif
        @required($required)
        {{ $attributes->except('id') }}
    >
        <option value=""></option>

        @if($options)
            {{-- Daftar tetap: dipakai filter admin. --}}
            @foreach($options as $option)
                <option value="{{ $option }}" @selected((string) $value === (string) $option)>
                    {{ $option }}
                </option>
            @endforeach
        @elseif($value && $selectedLabel)
            {{-- Pilihan yang sedang aktif, supaya tidak hilang saat form
                 dimuat ulang karena error validasi. --}}
            <option value="{{ $value }}" selected>{{ $selectedLabel }}</option>
        @endif
    </select>

    @error($name)
        {{-- Kelas sendiri, bukan Tailwind: komponen ini juga dipakai form
             publik yang hanya memuat Bootstrap. --}}
        <div class="ps-error">{{ $message }}</div>
    @enderror
</div>
