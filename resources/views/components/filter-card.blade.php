@props([
    'action',
    'keys' => [],
    'label' => 'Filter Data',
    'cols' => 'sm:grid-cols-2 lg:grid-cols-3',
])

{{--
    Panel filter yang baru terbuka setelah tombolnya diklik.

    Dipakai bersama halaman Tukar Faktur, Verifikasi, dan Billing supaya
    ketiganya punya bentuk dan perilaku yang sama.

        <x-filter-card :action="route('billing.index')" :keys="['status', 'search']">
            <x-form-field label="Cari">...</x-form-field>

            <x-slot name="actions">
                <x-button variant="outline" size="sm" href="...">CSV</x-button>
            </x-slot>
        </x-filter-card>

    Prop keys berisi query string yang dihitung sebagai filter aktif. Isinya
    menentukan tiga hal sekaligus: panel ikut terbuka saat halaman dimuat,
    badge penanda muncul, dan tombol Reset ditampilkan — jadi daftar ini
    harus memuat seluruh nama field di dalam slot.

    Slot actions muncul di baris tombol, sejajar dengan tombol filter, untuk
    aksi yang harus tetap terlihat walau panelnya tertutup (mis. export).
--}}
@php
    $adaFilter = request()->hasAny($keys);
@endphp

<div x-data="{ filter: {{ $adaFilter ? 'true' : 'false' }} }" {{ $attributes->twMerge('space-y-4') }}>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <x-button
                type="button"
                variant="outline"
                x-on:click="filter = ! filter"
                x-bind:aria-expanded="filter"
            >
                <x-icon name="funnel" />
                {{ $label }}
                <x-icon name="chevron-down" x-bind:class="filter && 'rotate-180'" class="transition-transform" />
            </x-button>

            @if ($adaFilter)
                <x-badge variant="secondary">Filter aktif</x-badge>
            @endif
        </div>

        @isset($actions)
            <div class="flex flex-wrap items-center gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>

    <x-card
        x-show="filter"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
    >
        <form method="GET" action="{{ $action }}">
            <x-card.content class="space-y-4 pt-6">

                <div class="grid gap-4 {{ $cols }}">
                    {{ $slot }}
                </div>

                <div class="flex flex-wrap justify-end gap-2 border-t pt-4">
                    @if ($adaFilter)
                        <x-button variant="ghost" :href="$action">
                            <x-icon name="rotate-ccw" />
                            Reset
                        </x-button>
                    @endif

                    <x-button type="submit">
                        <x-icon name="search" />
                        Terapkan
                    </x-button>
                </div>

            </x-card.content>
        </form>
    </x-card>

</div>
