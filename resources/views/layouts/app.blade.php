<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


</head>

<body class="bg-slate-100 text-slate-900 antialiased">

<div x-data="{ sidebar: false }" class="flex h-screen overflow-hidden">

    <!-- Overlay (mobile) -->
    <div
        x-show="sidebar"
        x-transition.opacity
        @click="sidebar = false"
        class="fixed inset-0 bg-black/30 z-40 lg:hidden">
    </div>

    <!-- SIDEBAR -->
    <aside
        :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
        class="fixed lg:static z-50 w-60 h-full bg-white border-r border-slate-200
               transform lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col"
    >
        <!-- Brand -->
        <div class="h-16 px-6 flex items-center font-semibold text-slate-800 tracking-tight">
            {{ config('app.name') }}
        </div>

        <!-- Menu -->
        <nav class="flex-1 px-3 space-y-1 text-sm">
            <a href="{{ route('dashboard') }}"
               class="group flex items-center gap-3 px-3 py-2 rounded-md
               {{ request()->routeIs('dashboard')
                    ? 'bg-slate-100 text-slate-900 font-medium'
                    : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-400 group-hover:bg-slate-600"></span>
                Dashboard
            </a>

            <a href="{{ route('admin.tukar-faktur.index') }}"
               class="group flex items-center gap-3 px-3 py-2 rounded-md
               {{ request()->routeIs('admin.tukar-faktur.*')
                    ? 'bg-slate-100 text-slate-900 font-medium'
                    : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-400 group-hover:bg-slate-600"></span>
                Tukar Faktur
            </a>

            <a href="{{ route('admin.perusahaan.index') }}"
               class="group flex items-center gap-3 px-3 py-2 rounded-md
               {{ request()->routeIs('admin.perusahaan.*')
                    ? 'bg-slate-100 text-slate-900 font-medium'
                    : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-400 group-hover:bg-slate-600"></span>
                Master Perusahaan
            </a>
        </nav>

        <!-- User -->
        <div class="px-4 py-4 border-t border-slate-200 text-sm">
            <div class="font-medium text-slate-800">
                {{ Auth::user()->name }}
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button class="text-slate-500 hover:text-slate-900">
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN -->
    <div class="flex-1 flex flex-col">

        <!-- TOPBAR -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6">
            <div class="flex items-center gap-4">
                <button @click="sidebar = !sidebar" class="lg:hidden text-slate-600">
                    ☰
                </button>

                @isset($header)
                    <div class="text-lg font-semibold text-slate-800">
                        {{ $header }}
                    </div>
                @endisset
            </div>
        </header>

        <!-- CONTENT -->
        <main class="flex-1 overflow-y-auto p-8">
            <div class="max-w-7xl mx-auto">
                {{ $slot }}
            </div>
        </main>

    </div>
</div>


@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: "{{ session('success') }}",
        confirmButtonColor: '#4f46e5'
    })
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: "{{ session('error') }}",
        confirmButtonColor: '#dc2626'
    })
</script>
@endif

</body>
</html>
