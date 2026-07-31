@props([
    'title' => null,
])

{{--
    Layout halaman publik yang diakses supplier tanpa login.

    Sengaja tidak memuat bundle app.js (Alpine + SweetAlert): halaman ini
    banyak dibuka dari ponsel dan tidak memerlukannya. Karena itu tidak ada
    tombol ganti tema di sini — temanya mengikuti preferensi sistem lewat
    <x-theme-script>.
--}}
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' — ' . config('app.name') : config('app.name') }}</title>

    <x-theme-script />

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Tom Select tidak lagi dimuat di sini: nama supplier pada form publik
         diketik manual dan dicek sama persis di server, bukan dipilih dari
         daftar. Bundle-nya hanya dipakai filter halaman admin. --}}
    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen bg-muted/40 font-sans text-foreground antialiased">
    <div class="mx-auto flex min-h-screen w-full max-w-2xl flex-col gap-6 px-4 py-8 sm:py-12">

        <div class="flex flex-col items-center gap-2 text-center">
            <span class="flex size-11 items-center justify-center rounded-xl bg-primary text-primary-foreground">
                <x-icon name="receipt-text" class="size-6" />
            </span>

            <h1 class="text-xl font-semibold tracking-tight">{{ $heading ?? 'Tukar Faktur Online' }}</h1>
            <p class="text-sm text-muted-foreground">Maharasa Group</p>
        </div>

        {{ $slot }}

        <p class="mt-auto pt-4 text-center text-xs text-muted-foreground">
            © {{ date('Y') }} Maharasa Group
        </p>
    </div>

    @stack('scripts')
</body>
</html>
