<?php

declare(strict_types=1);

namespace Tests\Feature\View;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ComponentSupportTest extends TestCase
{
    public function test_dropdown_merender_trigger_dan_isi_menu(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-dropdown>
                <x-slot name="trigger"><button>Buka</button></x-slot>
                <x-slot name="content">
                    <x-dropdown.label>Akun</x-dropdown.label>
                    <x-dropdown.separator />
                    <x-dropdown.item href="/profile" icon="user">Profil</x-dropdown.item>
                    <x-dropdown.item variant="destructive" type="submit">Keluar</x-dropdown.item>
                </x-slot>
            </x-dropdown>
        BLADE);

        $this->assertStringContainsString('Buka', $html);
        $this->assertStringContainsString('bg-popover', $html);
        $this->assertStringContainsString('<a href="/profile"', $html);
        $this->assertStringContainsString('role="separator"', $html);
        $this->assertStringContainsString('text-destructive', $html);
        $this->assertStringContainsString('type="submit"', $html);
    }

    public function test_dropdown_item_tanpa_href_jadi_button_bertipe_button(): void
    {
        $html = Blade::render('<x-dropdown.item>Aksi</x-dropdown.item>');

        $this->assertStringContainsString('<button type="button"', $html);
    }

    public function test_dialog_merender_overlay_dan_tombol_tutup(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-dialog name="hapus-data" max-width="md">
                <x-dialog.header>
                    <x-dialog.title>Hapus data</x-dialog.title>
                    <x-dialog.description>Tindakan ini permanen.</x-dialog.description>
                </x-dialog.header>
                <x-dialog.content>Isi</x-dialog.content>
                <x-dialog.footer>Aksi</x-dialog.footer>
            </x-dialog>
        BLADE);

        $this->assertStringContainsString('role="dialog"', $html);
        $this->assertStringContainsString('aria-modal="true"', $html);
        $this->assertStringContainsString('backdrop-blur-sm', $html);
        $this->assertStringContainsString('sm:max-w-md', $html);
        $this->assertStringContainsString('Tutup', $html);
        $this->assertStringContainsString('Hapus data', $html);
    }

    public function test_dialog_tak_bisa_ditutup_menyembunyikan_tombol_tutup(): void
    {
        $html = Blade::render('<x-dialog name="proses" :closeable="false">Isi</x-dialog>');

        $this->assertStringNotContainsString('Tutup', $html);
    }

    public function test_avatar_membentuk_inisial_dari_dua_kata_pertama(): void
    {
        $this->assertStringContainsString('BP', Blade::render('<x-avatar name="Budi Priyo Utomo" />'));
        $this->assertStringContainsString('S', Blade::render('<x-avatar name="Sari" />'));
        $this->assertStringContainsString('?', Blade::render('<x-avatar name="" />'));
    }

    public function test_breadcrumb_menandai_item_terakhir_sebagai_halaman_aktif(): void
    {
        $html = Blade::render('<x-breadcrumb :items="$items" />', [
            'items' => [
                ['label' => 'Tukar Faktur', 'url' => '/admin/tukar-faktur'],
                ['label' => 'Detail'],
            ],
        ]);

        $this->assertStringContainsString('<a href="/admin/tukar-faktur"', $html);
        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertStringContainsString('Detail', $html);
    }

    public function test_tabs_menandai_item_aktif(): void
    {
        $html = Blade::render('<x-tabs :active="$active" :items="$items" />', [
            'active' => 'email_sent',
            'items' => [
                ['label' => 'Semua', 'value' => null, 'url' => '/verifikasi'],
                ['label' => 'Menunggu', 'value' => 'email_sent', 'url' => '/verifikasi?status=email_sent', 'count' => 3],
            ],
        ]);

        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertStringContainsString('Menunggu', $html);
        $this->assertStringContainsString('>3<', $html);
    }

    public function test_separator_dan_skeleton(): void
    {
        $this->assertStringContainsString('h-full w-px', Blade::render('<x-separator orientation="vertical" />'));
        $this->assertStringContainsString('h-px w-full', Blade::render('<x-separator />'));
        $this->assertStringContainsString('animate-pulse', Blade::render('<x-skeleton class="h-4 w-32" />'));
    }

    public function test_theme_toggle_dan_script_tema(): void
    {
        $toggle = Blade::render('<x-theme-toggle />');
        $script = Blade::render('<x-theme-script />');

        $this->assertStringContainsString('localStorage', $toggle);
        $this->assertStringContainsString('<svg', $toggle);
        $this->assertStringContainsString("classList.toggle('dark'", $script);
        $this->assertStringContainsString('prefers-color-scheme: dark', $script);
    }

    public function test_paginator_memakai_view_shadcn(): void
    {
        $paginator = new LengthAwarePaginator(
            items: ['a', 'b'],
            total: 40,
            perPage: 20,
            currentPage: 2,
            options: ['path' => '/data'],
        );

        $html = (string) $paginator->links();

        $this->assertStringContainsString('Navigasi halaman', $html);
        $this->assertStringContainsString('Menampilkan', $html);
        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertStringContainsString('Berikutnya', $html);
    }

    public function test_paginator_sederhana_memakai_view_terpisah(): void
    {
        $paginator = new Paginator(
            items: ['a', 'b'],
            perPage: 2,
            currentPage: 1,
            options: ['path' => '/data'],
        );
        $paginator->hasMorePagesWhen(true);

        $html = (string) $paginator->links();

        $this->assertStringContainsString('Berikutnya', $html);
        $this->assertStringNotContainsString('Menampilkan', $html);
    }
}
