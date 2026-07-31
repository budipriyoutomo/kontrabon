<x-public-layout title="Tukar Faktur Berhasil">
    <x-slot name="heading">Tukar Faktur Terkirim</x-slot>

    <x-card class="animate-in fade-in slide-in-from-bottom-4 duration-500 motion-reduce:animate-none">
        <x-card.content class="space-y-5 pt-6 text-center">

            <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-success/10 text-success">
                <x-icon name="circle-check-big" class="size-8" />
            </div>

            <div class="space-y-1.5">
                <p class="text-lg font-semibold tracking-tight">Data berhasil dikirim</p>
                <p class="text-sm text-muted-foreground">
                    Terima kasih. Data tukar faktur sudah kami terima dan akan diproses oleh tim finance.
                </p>
            </div>

            <x-button href="/kontrabon" class="w-full">
                <x-icon name="rotate-cw" />
                Ajukan Tukar Faktur Lagi
            </x-button>

        </x-card.content>
    </x-card>
</x-public-layout>
