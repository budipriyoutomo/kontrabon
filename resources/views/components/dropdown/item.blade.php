@props([
    'href' => null,
    'variant' => null,
    'icon' => null,
])

{{--
    Satu baris menu. Tanpa href akan dirender sebagai <button>, sehingga bisa
    dipakai untuk aksi di dalam form (misalnya logout) maupun navigasi.
--}}
@php
    $classes = \App\Support\Cva::make(
        base: 'relative flex w-full cursor-pointer select-none items-center gap-2 rounded-sm px-2 py-1.5 '
            .'text-sm outline-none transition-colors focus:outline-none [&_svg]:size-4 [&_svg]:shrink-0',
        variants: [
            'variant' => [
                'default' => 'text-popover-foreground hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground',
                'destructive' => 'text-destructive hover:bg-destructive/10 focus:bg-destructive/10',
            ],
        ],
        defaultVariants: ['variant' => 'default'],
    )->resolve(['variant' => $variant]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->twMerge($classes) }}>
        @if ($icon)
            <x-icon :name="$icon" />
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $attributes->get('type', 'button') }}" {{ $attributes->except('type')->twMerge($classes) }}>
        @if ($icon)
            <x-icon :name="$icon" />
        @endif
        {{ $slot }}
    </button>
@endif
