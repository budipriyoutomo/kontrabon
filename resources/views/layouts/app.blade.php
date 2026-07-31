<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-dvh overflow-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — ' . config('app.name') : config('app.name', 'Laravel') }}</title>

    {{-- Harus paling awal supaya tema gelap tidak berkedip terang sesaat. --}}
    <x-theme-script />

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Alpine, SweetAlert, dan ikon Lucide semuanya ikut bundle; tidak ada CDN. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/perusahaan-select.js'])

    @stack('head')
</head>

<body class="h-full overflow-hidden bg-background font-sans text-foreground antialiased">

{{--
    Kerangka aplikasi: sidebar tinggi penuh + kolom konten yang menggulir sendiri.

    Dokumen sengaja dikunci: <html> dan <body> ikut overflow-hidden. Di layout
    app-shell hanya <main> yang boleh menggulir — kalau dokumen ikut bisa
    digulir, sidebar dan header ikut terdorong naik dan menyisakan ruang kosong
    di bawah konten. Layout guest dan publik punya <body> sendiri, jadi tidak
    ikut terkunci.

    Tinggi memakai dvh, bukan vh, supaya bilah alamat browser mobile yang muncul
    dan menghilang tidak memotong bagian bawah sidebar.
--}}
<div
    x-data="{ sidebar: false }"
    x-on:keydown.escape.window="sidebar = false"
    {{-- Panel geser tidak boleh tertinggal terbuka saat layar melebar ke desktop. --}}
    x-on:resize.window="if (window.innerWidth >= 1024) sidebar = false"
    class="flex h-full overflow-hidden"
>

    {{-- Latar gelap saat panel menu digeser masuk di layar kecil. --}}
    <div
        x-show="sidebar"
        x-transition.opacity
        x-on:click="sidebar = false"
        x-cloak
        aria-hidden="true"
        class="fixed inset-0 z-40 bg-foreground/50 backdrop-blur-sm lg:hidden"
    ></div>

    <aside
        id="sidebar-utama"
        :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 flex w-64 max-w-[85vw] -translate-x-full transform flex-col border-r border-sidebar-border bg-sidebar text-sidebar-foreground transition-transform duration-200 ease-in-out lg:static lg:h-full lg:max-w-none lg:translate-x-0"
    >
        <div class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border px-5">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-semibold tracking-tight">
                <span class="flex size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                    <x-icon name="receipt-text" />
                </span>
                <span class="truncate">{{ config('app.name') }}</span>
            </a>

            <button
                type="button"
                x-on:click="sidebar = false"
                class="rounded-md p-1 text-sidebar-foreground/70 hover:text-sidebar-foreground lg:hidden"
            >
                <x-icon name="x" />
                <span class="sr-only">Tutup menu</span>
            </button>
        </div>

        <x-sidebar.nav />

        {{-- pb-[env(...)] menjaga blok akun tetap di atas home indicator iOS. --}}
        <div class="shrink-0 border-t border-sidebar-border p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
            <div class="flex items-center gap-3 rounded-md px-2 py-1.5">
                <x-avatar :name="Auth::user()->name" size="sm" />

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium">{{ Auth::user()->name }}</p>
                    <p class="truncate text-xs text-muted-foreground">{{ Auth::user()->roleLabel() }}</p>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">

        <header class="flex h-16 shrink-0 items-center justify-between gap-2 border-b bg-background px-3 sm:gap-4 sm:px-6">
            <div class="flex min-w-0 flex-1 items-center gap-2 sm:gap-3">
                <button
                    type="button"
                    x-on:click="sidebar = true"
                    :aria-expanded="sidebar"
                    aria-controls="sidebar-utama"
                    class="{{ \App\View\Variants\ButtonVariants::classes('ghost', 'icon') }} shrink-0 lg:hidden"
                >
                    <x-icon name="panel-left" />
                    <span class="sr-only">Buka menu</span>
                </button>

                <div class="min-w-0 flex-1">
                    @isset($breadcrumb)
                        {{ $breadcrumb }}
                    @else
                        @isset($header)
                            <div class="truncate text-base font-semibold tracking-tight sm:text-lg">{{ $header }}</div>
                        @endisset
                    @endisset
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-1">
                <x-theme-toggle />

                <x-dropdown align="right" width="w-56">
                    <x-slot name="trigger">
                        <button type="button" class="{{ \App\View\Variants\ButtonVariants::classes('ghost', 'icon') }} rounded-full">
                            <x-avatar :name="Auth::user()->name" size="sm" />
                            <span class="sr-only">Menu akun</span>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown.label>
                            <span class="block truncate">{{ Auth::user()->name }}</span>
                            <span class="block truncate text-xs font-normal text-muted-foreground">
                                {{ Auth::user()->email }}
                            </span>
                        </x-dropdown.label>

                        <x-dropdown.separator />

                        <x-dropdown.item :href="route('profile.edit')" icon="user-round">
                            Profil
                        </x-dropdown.item>

                        <x-dropdown.separator />

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown.item type="submit" variant="destructive" icon="log-out">
                                Logout
                            </x-dropdown.item>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto overscroll-contain">
            <div class="mx-auto w-full max-w-7xl px-4 py-6 pb-[max(1.5rem,env(safe-area-inset-bottom))] sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>

    </div>
</div>

<x-flash />

@stack('scripts')

</body>
</html>
