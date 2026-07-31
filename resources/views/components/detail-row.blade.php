@props(['label'])

{{--
    Satu baris pasangan label-nilai untuk halaman detail. Pada layar sempit
    label dan nilai ditumpuk, di layar lebar label dikunci selebar 12rem.
--}}
<div {{ $attributes->twMerge('grid gap-1 py-2 text-sm sm:grid-cols-[12rem_1fr] sm:gap-6') }}>
    <dt class="text-muted-foreground">{{ $label }}</dt>
    <dd class="font-medium">{{ $slot }}</dd>
</div>
