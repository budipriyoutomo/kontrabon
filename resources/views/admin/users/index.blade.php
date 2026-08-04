@php
    $adaFilter = request()->hasAny(['search', 'role', 'status']);
@endphp

<x-app-layout>
    <x-slot name="title">Manajemen Pengguna</x-slot>
    <x-slot name="header">Manajemen Pengguna</x-slot>

    <div class="space-y-4">

        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <form
                method="GET"
                action="{{ route('admin.users.index') }}"
                class="flex w-full flex-col gap-2 sm:flex-row md:w-auto"
            >
                <div class="relative">
                    <x-icon
                        name="search"
                        class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
                    />
                    <x-input
                        type="text"
                        name="search"
                        :value="request('search')"
                        placeholder="Cari nama / email"
                        class="pl-9 sm:w-64"
                    />
                </div>

                <x-select name="role" class="sm:w-40">
                    <option value="">Semua peran</option>
                    @foreach ($roleOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                    @endforeach
                </x-select>

                <x-select name="status" class="sm:w-40">
                    <option value="">Semua status</option>
                    <option value="aktif" @selected(request('status') == 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(request('status') == 'nonaktif')>Nonaktif</option>
                </x-select>

                <div class="flex gap-2">
                    <x-button type="submit">Terapkan</x-button>

                    @if ($adaFilter)
                        <x-button variant="ghost" :href="route('admin.users.index')">
                            <x-icon name="rotate-ccw" />
                            Reset
                        </x-button>
                    @endif
                </div>
            </form>

            <x-button :href="route('admin.users.create')">
                <x-icon name="plus" />
                Tambah Pengguna
            </x-button>
        </div>

        <x-card class="overflow-hidden">
            <x-table>
                <x-table.header>
                    <x-table.row>
                        <x-table.sort-head column="name">Nama</x-table.sort-head>
                        <x-table.sort-head column="email">Email</x-table.sort-head>
                        <x-table.head>Peran</x-table.head>
                        <x-table.head>Status</x-table.head>
                        <x-table.head>Dibuat</x-table.head>
                        <x-table.head class="text-right">Aksi</x-table.head>
                    </x-table.row>
                </x-table.header>

                <x-table.body>
                    @forelse ($data as $row)
                        <x-table.row>
                            <x-table.cell>
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$row->name" size="sm" />

                                    <span class="font-medium">
                                        {{ $row->name }}

                                        @if ($row->id === auth()->id())
                                            <span class="text-xs font-normal text-muted-foreground">(Anda)</span>
                                        @endif
                                    </span>
                                </div>
                            </x-table.cell>

                            <x-table.cell class="text-muted-foreground">{{ $row->email }}</x-table.cell>

                            <x-table.cell>
                                <x-badge :variant="\App\View\Variants\BadgeVariants::forRole($row->role)">
                                    {{ $row->roleLabel() }}
                                </x-badge>
                            </x-table.cell>

                            <x-table.cell>
                                <x-badge :variant="$row->is_active ? 'success' : 'muted'">
                                    {{ $row->is_active ? 'Aktif' : 'Nonaktif' }}
                                </x-badge>
                            </x-table.cell>

                            <x-table.cell class="whitespace-nowrap text-muted-foreground">
                                {{ optional($row->created_at)->format('d/m/Y') ?: '-' }}
                            </x-table.cell>

                            <x-table.cell class="text-right">
                                @php
                                    $resetBag = \App\Http\Controllers\Admin\UserController::resetPasswordBag($row);
                                    $resetDialog = 'reset-password-' . $row->id;
                                @endphp

                                <div class="flex flex-wrap justify-end gap-1">
                                    <x-button variant="ghost" size="sm" :href="route('admin.users.edit', $row->id)">
                                        <x-icon name="pencil" />
                                        Edit
                                    </x-button>

                                    <x-button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        x-data
                                        x-on:click="$dispatch('open-modal', '{{ $resetDialog }}')"
                                    >
                                        <x-icon name="key-round" />
                                        Password
                                    </x-button>

                                    @if ($row->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.toggle-active', $row->id) }}">
                                            @csrf
                                            @method('PUT')

                                            <x-button type="submit" variant="ghost" size="sm">
                                                <x-icon :name="$row->is_active ? 'circle-pause' : 'circle-play'" />
                                                {{ $row->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </x-button>
                                        </form>

                                        <x-button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                                            onclick="window.ui.confirmDelete('{{ route('admin.users.destroy', $row->id) }}', { title: 'Hapus pengguna {{ $row->name }}?', text: 'Akun yang dihapus tidak bisa dikembalikan.' })"
                                        >
                                            <x-icon name="trash-2" />
                                            Hapus
                                        </x-button>
                                    @endif
                                </div>

                                {{-- Dialog dibuka lewat tombol "Password" di baris ini. Bila
                                     validasi gagal, dialog dibuka ulang otomatis lewat :show. --}}
                                <x-dialog :name="$resetDialog" :show="$errors->hasBag($resetBag)" max-width="md" focusable>
                                    <form method="POST" action="{{ route('admin.users.reset-password', $row->id) }}" class="text-left">
                                        @csrf
                                        @method('PUT')

                                        <x-dialog.header>
                                            <x-dialog.title>Buat Password Baru</x-dialog.title>
                                            <x-dialog.description>
                                                Password akun <span class="font-medium text-foreground">{{ $row->name }}</span>
                                                akan langsung diganti. Sampaikan password barunya ke pengguna terkait.
                                            </x-dialog.description>
                                        </x-dialog.header>

                                        <x-dialog.content class="space-y-4">
                                            <x-form-field
                                                label="Password Baru"
                                                name="password"
                                                :for="$resetDialog . '-password'"
                                                :messages="$errors->getBag($resetBag)->get('password')"
                                                required
                                            >
                                                <x-input
                                                    type="password"
                                                    :id="$resetDialog . '-password'"
                                                    name="password"
                                                    autocomplete="new-password"
                                                    required
                                                />
                                            </x-form-field>

                                            <x-form-field
                                                label="Ulangi Password Baru"
                                                name="password_confirmation"
                                                :for="$resetDialog . '-password-confirmation'"
                                                :messages="$errors->getBag($resetBag)->get('password_confirmation')"
                                                required
                                            >
                                                <x-input
                                                    type="password"
                                                    :id="$resetDialog . '-password-confirmation'"
                                                    name="password_confirmation"
                                                    autocomplete="new-password"
                                                    required
                                                />
                                            </x-form-field>
                                        </x-dialog.content>

                                        <x-dialog.footer>
                                            <x-button
                                                type="button"
                                                variant="outline"
                                                x-on:click="$dispatch('close-modal', '{{ $resetDialog }}')"
                                            >
                                                Batal
                                            </x-button>

                                            <x-button type="submit">Simpan Password</x-button>
                                        </x-dialog.footer>
                                    </form>
                                </x-dialog>
                            </x-table.cell>
                        </x-table.row>
                    @empty
                        <x-table.empty
                            :colspan="6"
                            icon="users"
                            :title="$adaFilter ? 'Tidak ada pengguna yang cocok' : 'Belum ada pengguna'"
                            :description="$adaFilter ? 'Coba ubah atau kosongkan filter pencarian.' : null"
                        />
                    @endforelse
                </x-table.body>
            </x-table>

            @if ($data->hasPages())
                <div class="border-t px-4 py-3">
                    {{ $data->appends(request()->query())->links() }}
                </div>
            @endif
        </x-card>

    </div>
</x-app-layout>
