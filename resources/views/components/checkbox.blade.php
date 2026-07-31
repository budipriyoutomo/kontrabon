@props(['disabled' => false])

{{--
    class form-checkbox berasal dari @tailwindcss/forms (strategy 'class') dan
    hanya dipakai untuk menggambar kotak centang; warnanya tetap dari token.
--}}
<input
    type="checkbox"
    {{ $disabled ? 'disabled' : '' }}
    {!! $attributes->twMerge(
        'form-checkbox size-4 shrink-0 rounded-sm border border-input bg-transparent text-primary '
        .'transition-colors focus:ring-0 focus:ring-offset-0 focus-visible:outline-none '
        .'focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 '
        .'disabled:cursor-not-allowed disabled:opacity-50'
    ) !!}
>
