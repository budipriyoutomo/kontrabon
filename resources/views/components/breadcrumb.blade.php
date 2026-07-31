@props(['items' => []])

{{--
    Jejak navigasi di topbar.

        <x-breadcrumb :items="[
            ['label' => 'Tukar Faktur', 'url' => route('admin.tukar-faktur.index')],
            ['label' => 'Detail'],
        ]" />

    Item terakhir dianggap halaman aktif dan tidak dirender sebagai tautan.
--}}
@php
    $items = collect($items)->values();
@endphp

<nav aria-label="Breadcrumb" {{ $attributes->twMerge('flex') }}>
    <ol class="flex flex-wrap items-center gap-1.5 text-sm text-muted-foreground">
        @foreach ($items as $index => $item)
            @php
                $isLast = $index === $items->count() - 1;
            @endphp

            <li class="inline-flex items-center gap-1.5">
                @if (! $isLast && ! empty($item['url']))
                    <a href="{{ $item['url'] }}" class="transition-colors hover:text-foreground">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span @class(['font-medium text-foreground' => $isLast]) @if ($isLast) aria-current="page" @endif>
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>

            @if (! $isLast)
                <li role="presentation">
                    <x-icon name="chevron-right" class="size-3.5" />
                </li>
            @endif
        @endforeach
    </ol>
</nav>
