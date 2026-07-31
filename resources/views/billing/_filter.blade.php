{{--
    Filter bersama halaman rekap & daftar billing.

    Tombol export ikut di slot actions supaya tetap terjangkau walau panel
    filternya sedang tertutup — keduanya mengikuti query string yang aktif.
--}}
<x-filter-card
    :action="$action"
    :keys="['start_bayar', 'end_bayar', 'pt_tujuan', 'perusahaan', 'status', 'search']"
>
    <x-slot name="actions">
        <x-button variant="outline" size="sm" :href="route('billing.export.csv', request()->query())">
            <x-icon name="file-down" />
            CSV
        </x-button>

        <x-button variant="outline" size="sm" :href="route('billing.export.pdf', request()->query())">
            <x-icon name="file-text" />
            PDF
        </x-button>
    </x-slot>

    <x-form-field label="Tanggal Bayar" class="sm:col-span-2 lg:col-span-1">
        <div class="flex items-center gap-2">
            <x-input type="date" name="start_bayar" :value="request('start_bayar')" aria-label="Tanggal bayar dari" />
            <span class="text-sm text-muted-foreground">s/d</span>
            <x-input type="date" name="end_bayar" :value="request('end_bayar')" aria-label="Tanggal bayar sampai" />
        </div>
    </x-form-field>

    <x-form-field label="PT Tujuan">
        <x-select name="pt_tujuan">
            <option value="">Semua PT</option>
            @foreach ($ptTujuanOptions as $option)
                <option value="{{ $option }}" @selected(request('pt_tujuan') === $option)>
                    {{ $option }}
                </option>
            @endforeach
        </x-select>
    </x-form-field>

    <x-form-field label="Status">
        <x-select name="status">
            <option value="">Semua (terverifikasi)</option>
            @foreach ($statusOptions as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </x-select>
    </x-form-field>

    <x-form-field label="Supplier">
        <x-perusahaan-select
            name="perusahaan"
            :value="request('perusahaan')"
            :options="$perusahaanOptions"
            placeholder="Semua supplier"
            ringkas
        />
    </x-form-field>

    <x-form-field label="Cari" class="sm:col-span-2">
        <div class="relative">
            <x-icon
                name="search"
                class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
            />
            <x-input
                type="text"
                name="search"
                :value="request('search')"
                placeholder="No kwitansi / supplier"
                class="pl-9"
            />
        </div>
    </x-form-field>
</x-filter-card>
