@props([
    'variant' => null,
    'size' => null,
    'href' => null,
])

{{--
    Tombol dengan varian ala shadcn/ui.

    Beri prop href untuk merender <a> yang tampil seperti tombol — dipakai
    untuk aksi navigasi seperti "Tambah Data" dan "Export Excel".

    Atribut type sengaja tidak diberi nilai bawaan supaya mengikuti perilaku
    HTML: <button> di dalam form berarti submit. Tombol yang tidak boleh
    submit harus menulis type="button" sendiri.
--}}
@php
    $classes = \App\View\Variants\ButtonVariants::classes($variant, $size);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->twMerge($classes) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->twMerge($classes) }}>
        {{ $slot }}
    </button>
@endif
