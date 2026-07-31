@props([
    'label',
    'value' => null,
    'icon' => null,
    'hint' => null,
])

{{--
    Kartu angka ringkas untuk dashboard dan panel ringkasan billing.
    Isi slot dipakai bila angkanya perlu markup sendiri, misalnya daftar rincian.
--}}
<x-card {{ $attributes }}>
    <x-card.header class="flex-row items-center justify-between space-y-0 pb-2">
        <x-card.description class="font-medium">{{ $label }}</x-card.description>

        @if ($icon)
            <x-icon :name="$icon" class="text-muted-foreground" />
        @endif
    </x-card.header>

    <x-card.content>
        @if ($value !== null)
            <p class="text-2xl font-semibold tabular-nums tracking-tight">{{ $value }}</p>
        @endif

        @if ($hint)
            <p class="mt-1 text-xs text-muted-foreground">{{ $hint }}</p>
        @endif

        {{ $slot }}
    </x-card.content>
</x-card>
