@props([
    'items' => [],
    'active' => null,
])

{{--
    Tab berbasis tautan untuk memfilter daftar berdasarkan status.

        <x-tabs :active="request('status')" :items="[
            ['label' => 'Semua', 'value' => null, 'url' => route('admin.verifikasi.index')],
            ['label' => 'Menunggu', 'value' => 'email_sent', 'url' => ..., 'count' => 3],
        ]" />

    Sengaja memakai <a>, bukan panel JavaScript, karena isi tiap tab datang
    dari query yang berbeda di server.
--}}
<div {{ $attributes->twMerge('inline-flex h-9 items-center justify-center gap-1 rounded-lg bg-muted p-1 text-muted-foreground') }}>
    @foreach ($items as $item)
        @php
            $isActive = ($item['value'] ?? null) === $active;
        @endphp

        <a
            href="{{ $item['url'] }}"
            @if ($isActive) aria-current="page" @endif
            @class([
                'inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-md px-3 py-1 text-sm font-medium transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                'bg-background text-foreground shadow-sm' => $isActive,
                'hover:text-foreground' => ! $isActive,
            ])
        >
            {{ $item['label'] }}

            @isset($item['count'])
                <span @class([
                    'rounded-full px-1.5 text-xs',
                    'bg-muted text-muted-foreground' => $isActive,
                    'bg-background/60' => ! $isActive,
                ])>{{ $item['count'] }}</span>
            @endisset
        </a>
    @endforeach
</div>
