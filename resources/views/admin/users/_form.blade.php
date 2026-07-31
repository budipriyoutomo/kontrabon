@php
    $user = $user ?? null;
    $isEdit = $user !== null;
    $isSelf = $isEdit && $user->id === auth()->id();
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
        <x-select name="role" required>
            <option value="">Pilih peran</option>
            @foreach ($roleOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $user->role?->value ?? '') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </x-select>

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
        :required="! $isEdit"
        :hint="$isEdit ? 'Kosongkan bila password tidak diubah.' : null"
    >
        <x-input type="password" name="password" autocomplete="new-password" @unless($isEdit) required @endunless />
    </x-form-field>

    <x-form-field label="Ulangi Password" name="password_confirmation" :required="! $isEdit">
        <x-input
            type="password"
            name="password_confirmation"
            autocomplete="new-password"
            @unless($isEdit) required @endunless
        />
    </x-form-field>

    <div class="space-y-1 sm:col-span-2">
        <label class="flex items-center gap-2 text-sm">
            {{-- Input tersembunyi menjaga field tetap terkirim saat kotak tidak dicentang. --}}
            <input type="hidden" name="is_active" value="0">

            <x-checkbox
                name="is_active"
                value="1"
                @checked(old('is_active', $user->is_active ?? true))
                @disabled($isSelf)
            />
            Akun aktif (boleh login)
        </label>

        @if ($isSelf)
            <p class="text-xs text-warning">Anda tidak dapat menonaktifkan akun sendiri.</p>
        @endif
    </div>

</div>
