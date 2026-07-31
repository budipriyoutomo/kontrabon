@php
    $navClass = \App\View\Variants\ButtonVariants::classes('outline', 'sm');
@endphp

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex items-center justify-end gap-2">
        @if ($paginator->onFirstPage())
            <span class="{{ $navClass }} pointer-events-none opacity-50" aria-disabled="true">
                <x-icon name="chevron-left" />
                Sebelumnya
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $navClass }}">
                <x-icon name="chevron-left" />
                Sebelumnya
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $navClass }}">
                Berikutnya
                <x-icon name="chevron-right" />
            </a>
        @else
            <span class="{{ $navClass }} pointer-events-none opacity-50" aria-disabled="true">
                Berikutnya
                <x-icon name="chevron-right" />
            </span>
        @endif
    </nav>
@endif
