@props([
    'value' => null,
    'required' => false,
])

<label {{ $attributes->twMerge('flex items-center gap-1 text-sm font-medium leading-none text-foreground peer-disabled:cursor-not-allowed peer-disabled:opacity-70') }}>
    {{ $value ?? $slot }}

    @if ($required)
        <span class="text-destructive" aria-hidden="true">*</span>
    @endif
</label>
