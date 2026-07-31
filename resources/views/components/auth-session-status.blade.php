@props(['status' => null])

@if ($status)
    <x-alert variant="success" icon="circle-check" {{ $attributes }}>
        <x-alert.description>{{ $status }}</x-alert.description>
    </x-alert>
@endif
