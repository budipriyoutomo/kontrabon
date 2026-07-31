@props([
    'colspan' => 1,
    'icon' => 'inbox',
    'title' => 'Belum ada data',
    'description' => null,
])

{{--
    Baris pengganti saat hasil query kosong. Dipakai di dalam <x-table.body>
    supaya tabel tetap punya bentuk dan lebar kolom yang sama.
--}}
<tr>
    <td colspan="{{ $colspan }}" class="px-4 py-12">
        <div class="flex flex-col items-center justify-center gap-2 text-center">
            <div class="flex size-10 items-center justify-center rounded-full bg-muted">
                <x-icon :name="$icon" class="size-5 text-muted-foreground" />
            </div>

            <p class="text-sm font-medium text-foreground">{{ $title }}</p>

            @if ($description)
                <p class="max-w-sm text-sm text-muted-foreground">{{ $description }}</p>
            @endif

            @if ($slot->isNotEmpty())
                <div class="mt-2">{{ $slot }}</div>
            @endif
        </div>
    </td>
</tr>
