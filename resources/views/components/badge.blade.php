@props([
    'variant' => null,
    'status' => null,
])

{{--
    Badge ala shadcn/ui.

    Beri prop status berisi TukarFakturStatus untuk memakai warna baku status
    tersebut, sehingga tidak perlu memilih varian secara manual di tiap tabel.
--}}
@php
    $resolvedVariant = $status !== null
        ? \App\View\Variants\BadgeVariants::forStatus($status)
        : $variant;

    $classes = \App\View\Variants\BadgeVariants::classes($resolvedVariant);
@endphp

<span {{ $attributes->twMerge($classes) }}>
    {{ $slot->isEmpty() && $status instanceof \App\Enums\TukarFakturStatus ? $status->label() : $slot }}
</span>
