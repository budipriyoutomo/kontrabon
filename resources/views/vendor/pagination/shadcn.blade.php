@php
    // Gaya tombol paginasi diambil dari definisi tombol yang sama dengan
    // <x-button> supaya tinggi, radius, dan ring fokusnya konsisten.
    $itemClass = \App\View\Variants\ButtonVariants::classes('ghost', 'icon');
    $activeClass = \App\View\Variants\ButtonVariants::classes('outline', 'icon');
    $navClass = \App\View\Variants\ButtonVariants::classes('ghost', 'icon');
@endphp

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex items-center justify-between gap-4">
        <p class="hidden text-sm text-muted-foreground sm:block">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
            dari {{ $paginator->total() }} data
        </p>

        <ul class="flex flex-1 items-center justify-end gap-1">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="{{ $navClass }} pointer-events-none opacity-50" aria-disabled="true">
                        <x-icon name="chevron-left" />
                        <span class="sr-only">Sebelumnya</span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $navClass }}">
                        <x-icon name="chevron-left" />
                        <span class="sr-only">Sebelumnya</span>
                    </a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li aria-hidden="true">
                        <span class="{{ $itemClass }} pointer-events-none">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span class="{{ $activeClass }} pointer-events-none" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="{{ $itemClass }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $navClass }}">
                        <x-icon name="chevron-right" />
                        <span class="sr-only">Berikutnya</span>
                    </a>
                @else
                    <span class="{{ $navClass }} pointer-events-none opacity-50" aria-disabled="true">
                        <x-icon name="chevron-right" />
                        <span class="sr-only">Berikutnya</span>
                    </span>
                @endif
            </li>
        </ul>
    </nav>
@endif
