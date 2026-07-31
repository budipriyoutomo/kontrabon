@props([
    'column',
    'align' => 'left',
])

{{--
    Kolom tabel yang bisa diurutkan.

    Arah urutan dibalik setiap kali kolom yang sedang aktif diklik lagi;
    kolom lain selalu mulai dari menaik. Seluruh query string lain ikut
    dipertahankan supaya filter tidak hilang saat mengurutkan.
--}}
@php
    $isActive = request('sort') === $column;
    $direction = $isActive && request('direction') === 'asc' ? 'desc' : 'asc';

    $url = request()->fullUrlWithQuery([
        'sort' => $column,
        'direction' => $direction,
        'page' => null,
    ]);
@endphp

<x-table.head {{ $attributes }}>
    <a
        href="{{ $url }}"
        @class([
            'inline-flex items-center gap-1 transition-colors hover:text-foreground',
            'flex-row-reverse' => $align === 'right',
        ])
    >
        {{ $slot }}

        @if ($isActive)
            <x-icon :name="request('direction') === 'asc' ? 'arrow-up' : 'arrow-down'" class="size-3.5" />
        @else
            <x-icon name="chevrons-up-down" class="size-3.5 opacity-40" />
        @endif
    </a>
</x-table.head>
