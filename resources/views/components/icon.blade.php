@props(['name'])

{{--
    Pembungkus tipis di atas blade-lucide-icons.

    Semua ikon di aplikasi dipanggil lewat komponen ini, bukan <x-lucide-*>
    langsung, supaya ukuran dan perilaku aksesibilitasnya seragam dan set
    ikonnya bisa diganti dari satu tempat.

    Ukuran bawaan size-4 mengikuti shadcn (16px, sejajar teks text-sm).
    Ikon bersifat dekoratif; beri prop aria-hidden="false" dan label sendiri
    bila ikon memang membawa makna yang tidak ada di teks sekitarnya.
--}}
<x-dynamic-component
    :component="'lucide-' . $name"
    {{ $attributes->twMerge('size-4 shrink-0')->merge(['aria-hidden' => 'true']) }}
/>
