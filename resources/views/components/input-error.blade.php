@props(['messages' => []])

@if ($messages)
    <ul {{ $attributes->twMerge('space-y-1 text-sm font-medium text-destructive') }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
