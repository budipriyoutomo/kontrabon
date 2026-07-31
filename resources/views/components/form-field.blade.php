@props([
    'label' => null,
    'name' => null,
    'for' => null,
    'hint' => null,
    'required' => false,
    'messages' => null,
])

{{--
    Pembungkus satu baris form: label, kontrol, keterangan, dan pesan error.

    Pesan error diambil otomatis dari validasi berdasarkan prop name, jadi
    pemakaiannya cukup:

        <x-form-field label="Nama Supplier" name="supplier" required>
            <x-input name="supplier" :value="old('supplier')" />
        </x-form-field>
--}}
@php
    // $errors hanya tersedia lewat middleware ShareErrorsFromSession, jadi
    // keberadaannya diperiksa agar komponen tetap aman dipakai di luar
    // konteks web, misalnya saat merender PDF.
    $fieldErrors = $messages ?? (($name && isset($errors)) ? $errors->get($name) : []);
    $labelFor = $for ?? $name;
@endphp

<div {{ $attributes->twMerge('space-y-2') }}>
    @if ($label)
        <x-label :for="$labelFor" :required="$required">{{ $label }}</x-label>
    @endif

    {{ $slot }}

    @if ($hint)
        <p class="text-xs text-muted-foreground">{{ $hint }}</p>
    @endif

    <x-input-error :messages="$fieldErrors" />
</div>
