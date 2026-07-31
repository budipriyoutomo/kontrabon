{{--
    Permukaan konten utama, mengikuti anatomi card shadcn/ui.

    Susunan lengkapnya:

        <x-card>
            <x-card.header>
                <x-card.title>Judul</x-card.title>
                <x-card.description>Penjelasan singkat</x-card.description>
            </x-card.header>
            <x-card.content>...</x-card.content>
            <x-card.footer>...</x-card.footer>
        </x-card>
--}}
<div {{ $attributes->twMerge('rounded-xl border bg-card text-card-foreground shadow-sm') }}>
    {{ $slot }}
</div>
