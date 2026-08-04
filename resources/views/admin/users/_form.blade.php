@php
    $user = $user ?? null;
    $isEdit = $user !== null;
    $isSelf = $isEdit && $user->id === auth()->id();

    // Nilai boolean dihitung di sini, bukan lewat direktif @checked/@unless di
    // dalam tag komponen: Blade hanya mengizinkan @class dan @style di posisi
    // itu, direktif lain membuat tag <x-...> gagal dikompilasi dan ikut
    // tercetak mentah ke HTML.
    $passwordWajib = ! $isEdit;
    $aktifTercentang = (bool) old('is_active', $user->is_active ?? true);
@endphp

@if ($errors->any())
    <x-alert variant="destructive" icon="triangle-alert" class="mb-6">
        <x-alert.title>Data belum tersimpan</x-alert.title>
        <x-alert.description>
            <ul class="mt-1 list-disc space-y-1 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert.description>
    </x-alert>
@endif

<div class="grid gap-4 sm:grid-cols-2">

    <x-form-field label="Nama" name="name" required>
        <x-input type="text" name="name" :value="old('name', $user->name ?? '')" required />
    </x-form-field>

    <x-form-field label="Email" name="email" required>
        <x-input type="email" name="email" :value="old('email', $user->email ?? '')" required />
    </x-form-field>

    <x-form-field label="Peran" name="role" required class="sm:col-span-2">
        {{-- Peran akun sendiri dikunci agar admin tidak menurunkan haknya
             sendiri. Select-nya dinonaktifkan, jadi nilainya dikirim lewat
             input tersembunyi supaya form tetap lolos validasi. --}}
        @if ($isSelf)
            <input type="hidden" name="role" value="{{ $user->role?->value }}">
        @endif

        <x-select name="role" required :disabled="$isSelf">
            <option value="">Pilih peran</option>
            @foreach ($roleOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $user->role?->value ?? '') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </x-select>

        @if ($isSelf)
            <p class="text-xs text-warning">Anda tidak dapat mengubah peran akun sendiri.</p>
        @endif

        <ul class="space-y-0.5 text-xs text-muted-foreground">
            @foreach (\App\Enums\UserRole::cases() as $role)
                <li>
                    <span class="font-medium text-foreground">{{ $role->label() }}</span>
                    — {{ $role->description() }}
                </li>
            @endforeach
        </ul>
    </x-form-field>

    <x-form-field
        label="Password"
        name="password"
        :required="$passwordWajib"
        :hint="$isEdit ? 'Kosongkan bila password tidak diubah.' : null"
    >
        <x-input type="password" name="password" autocomplete="new-password" :required="$passwordWajib" />
    </x-form-field>

    <x-form-field label="Ulangi Password" name="password_confirmation" :required="$passwordWajib">
        <x-input
            type="password"
            name="password_confirmation"
            autocomplete="new-password"
            :required="$passwordWajib"
        />
    </x-form-field>

    <div class="space-y-1 sm:col-span-2">
        <label class="flex items-center gap-2 text-sm">
            {{-- Input tersembunyi menjaga field tetap terkirim saat kotak tidak dicentang. --}}
            <input type="hidden" name="is_active" value="0">

            <x-checkbox
                name="is_active"
                value="1"
                :checked="$aktifTercentang"
                :disabled="$isSelf"
            />
            Akun aktif (boleh login)
        </label>

        @if ($isSelf)
            <p class="text-xs text-warning">Anda tidak dapat menonaktifkan akun sendiri.</p>
        @endif
    </div>

</div>
