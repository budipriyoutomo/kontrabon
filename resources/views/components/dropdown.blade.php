@props([
    'align' => 'right',
    'width' => 'w-56',
    'contentClasses' => null,
])

{{--
    Dropdown menu ala shadcn/ui, digerakkan Alpine.

    Slot trigger dan content dipertahankan dari versi Breeze supaya pemakaian
    lama tetap jalan; isinya diisi <x-dropdown.item>, <x-dropdown.label>, dan
    <x-dropdown.separator>.

    Nilai width menerima kelas Tailwind langsung (w-56, w-72, ...) agar tidak
    perlu daftar ukuran yang dipetakan manual.
--}}
@php
    $alignmentClasses = match ($align) {
        'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
        'top' => 'origin-top',
        default => 'ltr:origin-top-right rtl:origin-top-left end-0',
    };

    // Beberapa pemakaian lama mengirim contentClasses berisi warna latar
    // Breeze; kalau tidak diisi, dipakai gaya popover dari design token.
    $panelClasses = $contentClasses ?? 'bg-popover text-popover-foreground';
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 {{ $width }} {{ $alignmentClasses }}"
        style="display: none;"
        @click="open = false"
    >
        <div {{ $attributes->twMerge('overflow-hidden rounded-md border p-1 shadow-md ' . $panelClasses) }}>
            {{ $content }}
        </div>
    </div>
</div>
