@props(['disabled' => false])

{{--
    Select native yang digayakan agar setara <x-input>.

    Panah bawaan browser dimatikan (appearance-none) lalu digambar ulang lewat
    class .select-chevron di app.css, karena warna SVG di dalam data URI tidak
    bisa membaca CSS variable sehingga versi terang dan gelapnya harus dipisah.
--}}
<select
    {{ $disabled ? 'disabled' : '' }}
    {!! $attributes->twMerge(
        'select-chevron flex h-9 w-full appearance-none rounded-md border border-input bg-transparent py-1 '
        .'pl-3 pr-9 text-sm shadow-sm transition-colors focus-visible:outline-none '
        .'focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50'
    ) !!}
>{{ $slot }}</select>
