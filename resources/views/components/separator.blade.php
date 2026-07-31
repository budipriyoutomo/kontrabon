@props(['orientation' => 'horizontal'])

<div
    role="separator"
    aria-orientation="{{ $orientation }}"
    {{ $attributes->twMerge('shrink-0 bg-border ' . ($orientation === 'vertical' ? 'h-full w-px' : 'h-px w-full')) }}
></div>
