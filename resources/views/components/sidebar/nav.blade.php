{{--
    Daftar menu sidebar.

    Dipisah dari layout supaya panel desktop dan panel geser versi mobile
    memakai satu sumber menu yang sama.
--}}
{{-- min-h-0 wajib: tanpa itu nav ikut memanjang dan sidebar tidak jadi tinggi penuh. --}}
<nav aria-label="Navigasi utama" class="min-h-0 flex-1 space-y-1 overflow-y-auto overscroll-contain px-3 py-4">
    <x-sidebar.item
        :href="route('dashboard')"
        icon="layout-dashboard"
        :active="request()->routeIs('dashboard')"
    >
        Dashboard
    </x-sidebar.item>

    @can('viewAny', App\Models\TukarFaktur::class)
        <x-sidebar.item
            :href="route('admin.tukar-faktur.index')"
            icon="receipt-text"
            :active="request()->routeIs('admin.tukar-faktur.*')"
        >
            Tukar Faktur
        </x-sidebar.item>
    @endcan

    @if (auth()->user()->hasRole(App\Enums\UserRole::Verifikator) || auth()->user()->isAdmin())
        <x-sidebar.item
            :href="route('admin.verifikasi.index')"
            icon="badge-check"
            :active="request()->routeIs('admin.verifikasi.*')"
        >
            Verifikasi
        </x-sidebar.item>
    @endif

    @can('viewAny', App\Models\Perusahaan::class)
        <x-sidebar.item
            :href="route('admin.perusahaan.index')"
            icon="building-2"
            :active="request()->routeIs('admin.perusahaan.*')"
        >
            Master Perusahaan
        </x-sidebar.item>
    @endcan

    @can('viewBilling', App\Models\TukarFaktur::class)
        <x-sidebar.item
            :href="route('billing.index')"
            icon="wallet"
            :active="request()->routeIs('billing.*')"
        >
            Billing
        </x-sidebar.item>
    @endcan

    @can('viewAny', App\Models\User::class)
        <x-sidebar.item
            :href="route('admin.users.index')"
            icon="users"
            :active="request()->routeIs('admin.users.*')"
        >
            Manajemen Pengguna
        </x-sidebar.item>
    @endcan
</nav>
