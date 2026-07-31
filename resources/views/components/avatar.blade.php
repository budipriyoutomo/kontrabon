@props([
    'name' => '',
    'size' => 'default',
])

{{--
    Avatar berbasis inisial. Belum ada unggah foto profil di aplikasi ini,
    jadi yang ditampilkan adalah dua huruf pertama dari nama.
--}}
@php
    $initials = collect(preg_split('/\s+/', trim((string) $name)))
        ->filter()
        ->take(2)
        ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');

    $sizeClass = [
        'sm' => 'size-7 text-xs',
        'default' => 'size-9 text-sm',
        'lg' => 'size-12 text-base',
    ][$size];
@endphp

<span {{ $attributes->twMerge('inline-flex shrink-0 select-none items-center justify-center rounded-full bg-muted font-medium text-muted-foreground ' . $sizeClass) }}>
    {{ $initials !== '' ? $initials : '?' }}
</span>
