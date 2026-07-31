@props([
    'href',
    'icon' => null,
    'active' => false,
])

<a
    href="{{ $href }}"
    @if ($active) aria-current="page" @endif
    @class([
        'group flex items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors',
        'bg-sidebar-accent font-medium text-sidebar-accent-foreground' => $active,
        'text-sidebar-foreground/70 hover:bg-sidebar-accent/60 hover:text-sidebar-foreground' => ! $active,
    ])
>
    @if ($icon)
        <x-icon :name="$icon" class="size-4" />
    @endif

    <span class="truncate">{{ $slot }}</span>
</a>
