<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — ' . config('app.name') : config('app.name', 'Laravel') }}</title>

    <x-theme-script />

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-muted/40 font-sans text-foreground antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center gap-6 px-4 py-10">

        <a href="/" class="flex items-center gap-2 text-lg font-semibold tracking-tight">
            <span class="flex size-9 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                <x-icon name="receipt-text" class="size-5" />
            </span>
            {{ config('app.name') }}
        </a>

        <x-card class="w-full max-w-md shadow-lg">
            @isset($heading)
                <x-card.header>
                    <x-card.title as="h1" class="text-xl">{{ $heading }}</x-card.title>

                    @isset($description)
                        <x-card.description>{{ $description }}</x-card.description>
                    @endisset
                </x-card.header>
            @endisset

            <x-card.content @class(['pt-6' => ! isset($heading)])>
                {{ $slot }}
            </x-card.content>
        </x-card>

        <div class="fixed right-4 top-4">
            <x-theme-toggle />
        </div>
    </div>
</body>
</html>
