@props([
    'variant' => null,
    'icon' => null,
])

{{--
    Pesan kontekstual di dalam halaman (bukan dialog).

        <x-alert variant="info" icon="info">
            <x-alert.title>Menunggu verifikasi</x-alert.title>
            <x-alert.description>Ada 3 data yang emailnya sudah terkirim.</x-alert.description>
        </x-alert>
--}}
<div role="alert" {{ $attributes->twMerge(\App\View\Variants\AlertVariants::classes($variant)) }}>
    @if ($icon)
        <x-icon :name="$icon" />
    @endif

    {{ $slot }}
</div>
