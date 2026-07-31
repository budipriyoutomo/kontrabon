<?php

declare(strict_types=1);

namespace Tests\Feature\View;

use App\Enums\TukarFakturStatus;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * Uji asap untuk pustaka komponen: memastikan tiap komponen benar-benar
 * merender elemen yang diharapkan dan varian utamanya berpengaruh.
 */
class ComponentRenderTest extends TestCase
{
    public function test_button_merender_elemen_button(): void
    {
        $html = Blade::render('<x-button>Simpan</x-button>');

        $this->assertStringContainsString('<button', $html);
        $this->assertStringContainsString('Simpan', $html);
        $this->assertStringContainsString('bg-primary', $html);
        $this->assertStringContainsString('h-9', $html);
    }

    public function test_button_dengan_href_merender_anchor(): void
    {
        $html = Blade::render('<x-button href="/billing" variant="outline" size="sm">Billing</x-button>');

        $this->assertStringContainsString('<a href="/billing"', $html);
        $this->assertStringNotContainsString('<button', $html);
        $this->assertStringContainsString('border-input', $html);
        $this->assertStringContainsString('h-8', $html);
        $this->assertStringNotContainsString('h-9', $html);
    }

    public function test_button_tidak_memaksa_type_agar_submit_tetap_jalan(): void
    {
        $this->assertStringNotContainsString('type=', Blade::render('<x-button>Kirim</x-button>'));
        $this->assertStringContainsString('type="button"', Blade::render('<x-button type="button">Tutup</x-button>'));
    }

    public function test_badge_memakai_warna_status_tukar_faktur(): void
    {
        $html = Blade::render('<x-badge :status="$status" />', [
            'status' => TukarFakturStatus::Verified,
        ]);

        $this->assertStringContainsString('Terverifikasi', $html);
        $this->assertStringContainsString('text-success', $html);
    }

    public function test_badge_status_pending_dan_email_sent_berbeda_warna(): void
    {
        $pending = Blade::render('<x-badge :status="$status" />', ['status' => TukarFakturStatus::Pending]);
        $emailSent = Blade::render('<x-badge :status="$status" />', ['status' => TukarFakturStatus::EmailSent]);

        $this->assertStringContainsString('text-warning', $pending);
        $this->assertStringContainsString('text-info', $emailSent);
    }

    public function test_card_dan_sub_komponennya(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-card>
                <x-card.header>
                    <x-card.title>Judul</x-card.title>
                    <x-card.description>Keterangan</x-card.description>
                </x-card.header>
                <x-card.content>Isi</x-card.content>
                <x-card.footer>Kaki</x-card.footer>
            </x-card>
        BLADE);

        $this->assertStringContainsString('bg-card', $html);
        $this->assertStringContainsString('<h3', $html);
        $this->assertStringContainsString('Judul', $html);
        $this->assertStringContainsString('text-muted-foreground', $html);
        $this->assertStringContainsString('Isi', $html);
        $this->assertStringContainsString('Kaki', $html);
    }

    public function test_card_title_bisa_ganti_tag(): void
    {
        $html = Blade::render('<x-card.title as="h2">Judul</x-card.title>');

        $this->assertStringContainsString('<h2', $html);
        $this->assertStringContainsString('</h2>', $html);
    }

    public function test_tabel_merender_struktur_lengkap(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-table>
                <x-table.header>
                    <x-table.row><x-table.head>Nomor</x-table.head></x-table.row>
                </x-table.header>
                <x-table.body>
                    <x-table.row><x-table.cell>001</x-table.cell></x-table.row>
                </x-table.body>
            </x-table>
        BLADE);

        foreach (['<table', '<thead', '<tbody', '<tr', '<th', '<td'] as $tag) {
            $this->assertStringContainsString($tag, $html);
        }

        $this->assertStringContainsString('overflow-x-auto', $html);
    }

    public function test_baris_kosong_tabel(): void
    {
        $html = Blade::render('<x-table.empty :colspan="5" title="Data tidak ditemukan" description="Ubah filter pencarian." />');

        $this->assertStringContainsString('colspan="5"', $html);
        $this->assertStringContainsString('Data tidak ditemukan', $html);
        $this->assertStringContainsString('Ubah filter pencarian.', $html);
        $this->assertStringContainsString('<svg', $html);
    }

    public function test_alert_dengan_varian_dan_ikon(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-alert variant="info" icon="info">
                <x-alert.title>Perhatian</x-alert.title>
                <x-alert.description>Ada data menunggu verifikasi.</x-alert.description>
            </x-alert>
        BLADE);

        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('text-info', $html);
        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('Perhatian', $html);
    }

    public function test_form_field_mengambil_pesan_error_dari_validasi(): void
    {
        $html = $this->withViewErrors(['supplier' => 'Supplier wajib diisi.'])
            ->blade('<x-form-field label="Supplier" name="supplier" required><x-input name="supplier" /></x-form-field>');

        $html = $html->__toString();

        $this->assertStringContainsString('Supplier wajib diisi.', $html);
        $this->assertStringContainsString('text-destructive', $html);
        $this->assertStringContainsString('<label', $html);
        $this->assertStringContainsString('for="supplier"', $html);
        $this->assertStringContainsString('<input', $html);
    }

    public function test_form_field_tanpa_error_tidak_merender_daftar_pesan(): void
    {
        $html = Blade::render('<x-form-field label="Supplier" name="supplier"><x-input name="supplier" /></x-form-field>');

        $this->assertStringNotContainsString('<ul', $html);
        $this->assertStringContainsString('Supplier', $html);
    }

    public function test_kontrol_form_memakai_gaya_token(): void
    {
        foreach (['<x-input />', '<x-textarea />', '<x-select />', '<x-checkbox />'] as $component) {
            $this->assertStringContainsString('focus-visible:ring-ring', Blade::render($component), $component);
        }

        $this->assertStringContainsString('select-chevron', Blade::render('<x-select />'));
    }
}
