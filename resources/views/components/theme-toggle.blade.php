{{--
    Pengalih tema terang/gelap.

    Pilihan "system" tidak dibuatkan tombol tersendiri: selama pengguna belum
    pernah memilih, tema mengikuti preferensi sistem (lihat <x-theme-script>).
    Menekan tombol ini menetapkan pilihan tetap di localStorage.
--}}
<button
    type="button"
    x-data="{
        dark: document.documentElement.classList.contains('dark'),
        toggle() {
            this.dark = ! this.dark;
            document.documentElement.classList.toggle('dark', this.dark);

            try {
                localStorage.setItem('theme', this.dark ? 'dark' : 'light');
            } catch (e) {
                // Preferensi tidak bisa disimpan; tema tetap berganti untuk sesi ini.
            }
        },
    }"
    x-on:click="toggle()"
    :aria-label="dark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap'"
    {{ $attributes->twMerge(\App\View\Variants\ButtonVariants::classes('ghost', 'icon')) }}
>
    <x-icon name="sun" x-show="dark" x-cloak />
    <x-icon name="moon" x-show="! dark" x-cloak />
</button>
