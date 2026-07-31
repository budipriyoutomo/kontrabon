@props(['as' => 'h3'])

<{{ $as }} {{ $attributes->twMerge('font-semibold leading-none tracking-tight') }}>
    {{ $slot }}
</{{ $as }}>
