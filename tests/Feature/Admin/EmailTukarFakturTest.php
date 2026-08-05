<?php

namespace Tests\Feature\Admin;

use App\Enums\TukarFakturStatus;
use App\Jobs\KirimEmailTukarFaktur;
use App\Mail\TukarFakturMail;
use App\Enums\UserRole;
use App\Models\Perusahaan;
use App\Models\TukarFaktur;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

/**
 * Status email_sent adalah dasar verifikator bekerja, jadi status itu tidak
 * boleh naik kecuali emailnya benar-benar terkirim.
 */
class EmailTukarFakturTest extends TestCase
{
    use RefreshDatabase;

    private string $filePath = 'tukar-faktur/bukti.pdf';

    private function data(string $status = 'pending'): TukarFaktur
    {
        $perusahaan = Perusahaan::create([
            'nama' => 'PT Vendor Email',
            'kode' => 'VND-EMAIL',
            'top' => 30,
            'is_active' => true,
        ]);

        return TukarFaktur::create([
            'pt_tujuan' => 'PT Maharasa Jaya Abadi (Pepper Lunch)',
            'perusahaan_id' => $perusahaan->id,
            'perusahaan_pengaju' => $perusahaan->nama,
            'tanggal_tukar' => now()->toDateString(),
            'tanggal_pembayaran' => now()->addDays(30)->toDateString(),
            'no_kwitansi' => 'KW-EMAIL-1',
            'jumlah_rupiah' => 2_500_000,
            'nama_pic' => 'PIC Email',
            'email_penerima' => 'pic@vendor.test',
            'status' => $status,
        ]);
    }

    private function siapkanPdf(): void
    {
        Storage::fake('local');
        Storage::put($this->filePath, '%PDF-1.4 dummy');
    }

    public function test_status_naik_setelah_email_benar_benar_terkirim(): void
    {
        $this->siapkanPdf();
        Mail::fake();

        $data = $this->data();

        (new KirimEmailTukarFaktur((string) $data->id, $this->filePath))->handle();

        Mail::assertSent(
            TukarFakturMail::class,
            fn (TukarFakturMail $mail) => $mail->hasTo('pic@vendor.test')
        );

        $this->assertSame(TukarFakturStatus::EmailSent, $data->fresh()->status);
    }

    public function test_status_tetap_pending_saat_smtp_gagal(): void
    {
        $this->siapkanPdf();

        Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('SMTP tidak bisa dihubungi'));

        $data = $this->data();

        try {
            (new KirimEmailTukarFaktur((string) $data->id, $this->filePath))->handle();
            $this->fail('Job seharusnya melempar exception supaya bisa dicoba ulang.');
        } catch (RuntimeException $e) {
            $this->assertSame('SMTP tidak bisa dihubungi', $e->getMessage());
        }

        $this->assertSame(TukarFakturStatus::Pending, $data->fresh()->status);
    }

    public function test_status_tetap_pending_saat_pdf_hilang(): void
    {
        Storage::fake('local');
        Mail::fake();

        $data = $this->data();

        try {
            (new KirimEmailTukarFaktur((string) $data->id, $this->filePath))->handle();
            $this->fail('Job seharusnya melempar exception saat PDF tidak ada.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('PDF tukar faktur tidak ditemukan', $e->getMessage());
        }

        Mail::assertNothingSent();
        $this->assertSame(TukarFakturStatus::Pending, $data->fresh()->status);
    }

    public function test_job_kembar_tidak_mengirim_email_dua_kali(): void
    {
        $this->siapkanPdf();
        Mail::fake();

        $data = $this->data(TukarFakturStatus::Verified->value);

        (new KirimEmailTukarFaktur((string) $data->id, $this->filePath))->handle();

        Mail::assertNothingSent();
        $this->assertSame(TukarFakturStatus::Verified, $data->fresh()->status);
    }

    public function test_data_terhapus_tidak_membuat_job_gagal(): void
    {
        $this->siapkanPdf();
        Mail::fake();

        (new KirimEmailTukarFaktur('00000000-0000-0000-0000-000000000000', $this->filePath))->handle();

        Mail::assertNothingSent();
    }

    public function test_kirim_ulang_tidak_mengubah_status_terverifikasi(): void
    {
        $this->siapkanPdf();
        Mail::fake();

        $data = $this->data(TukarFakturStatus::Verified->value);

        (new KirimEmailTukarFaktur((string) $data->id, $this->filePath, kirimUlang: true))->handle();

        Mail::assertSent(
            TukarFakturMail::class,
            fn (TukarFakturMail $mail) => $mail->hasTo('pic@vendor.test')
        );

        $this->assertSame(TukarFakturStatus::Verified, $data->fresh()->status);
    }

    public function test_kirim_ulang_atas_data_email_sent_tetap_email_sent(): void
    {
        $this->siapkanPdf();
        Mail::fake();

        $data = $this->data(TukarFakturStatus::EmailSent->value);

        (new KirimEmailTukarFaktur((string) $data->id, $this->filePath, kirimUlang: true))->handle();

        Mail::assertSentCount(1);
        $this->assertSame(TukarFakturStatus::EmailSent, $data->fresh()->status);
    }

    public function test_kirim_ulang_ditolak_untuk_data_yang_masih_pending(): void
    {
        $this->siapkanPdf();
        Mail::fake();

        $data = $this->data();

        (new KirimEmailTukarFaktur((string) $data->id, $this->filePath, kirimUlang: true))->handle();

        Mail::assertNothingSent();
        $this->assertSame(TukarFakturStatus::Pending, $data->fresh()->status);
    }

    /**
     * dompdf butuh ekstensi gd untuk logo PNG di template, jadi rendernya
     * dipalsukan agar pengujian rute tidak bergantung pada konfigurasi PHP
     * mesin yang menjalankannya.
     */
    private function palsukanPdf(): void
    {
        Storage::fake('local');

        $pdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf->shouldReceive('output')->andReturn('%PDF-1.4 dummy');

        Pdf::shouldReceive('loadView')->once()->andReturn($pdf);
    }

    public function test_kontrabon_bisa_meminta_kirim_ulang_lewat_halaman_detail(): void
    {
        $this->palsukanPdf();
        Queue::fake();

        $data = $this->data(TukarFakturStatus::EmailSent->value);

        $this->actingAs(User::factory()->role(UserRole::Kontrabon)->create())
            ->from(route('admin.tukar-faktur.show', $data->id))
            ->post(route('admin.tukar-faktur.resend-email', $data->id))
            ->assertRedirect(route('admin.tukar-faktur.show', $data->id))
            ->assertSessionHas('success');

        Queue::assertPushed(
            KirimEmailTukarFaktur::class,
            fn (KirimEmailTukarFaktur $job) => $job->tukarFakturId === (string) $data->id
                && $job->kirimUlang === true
        );

        $this->assertSame(TukarFakturStatus::EmailSent, $data->fresh()->status);
    }

    public function test_kirim_ulang_ditolak_lewat_rute_saat_masih_pending(): void
    {
        Queue::fake();

        $data = $this->data();

        $this->actingAs(User::factory()->role(UserRole::Kontrabon)->create())
            ->from(route('admin.tukar-faktur.show', $data->id))
            ->post(route('admin.tukar-faktur.resend-email', $data->id))
            ->assertRedirect(route('admin.tukar-faktur.show', $data->id))
            ->assertSessionHas('info');

        Queue::assertNothingPushed();
    }

    public function test_verifikator_tidak_bisa_kirim_ulang(): void
    {
        Queue::fake();

        $data = $this->data(TukarFakturStatus::EmailSent->value);

        $this->actingAs(User::factory()->role(UserRole::Verifikator)->create())
            ->post(route('admin.tukar-faktur.resend-email', $data->id))
            ->assertForbidden();

        Queue::assertNothingPushed();
    }

    public function test_tombol_kirim_ulang_hanya_muncul_setelah_email_terkirim(): void
    {
        $kontrabon = User::factory()->role(UserRole::Kontrabon)->create();

        $pending = $this->data();

        $this->actingAs($kontrabon)
            ->get(route('admin.tukar-faktur.show', $pending->id))
            ->assertOk()
            ->assertDontSee('Kirim Ulang Email');

        $pending->update(['status' => TukarFakturStatus::EmailSent]);

        $this->actingAs($kontrabon)
            ->get(route('admin.tukar-faktur.show', $pending->id))
            ->assertOk()
            ->assertSee('Kirim Ulang Email')
            ->assertSee(route('admin.tukar-faktur.resend-email', $pending->id), escape: false);
    }

    /**
     * Dulu status dinaikkan di __destruct() milik Mailable, sehingga ikut
     * berubah walau emailnya belum pernah dikirim. Mailable sekarang harus
     * benar-benar pasif.
     */
    public function test_mailable_tidak_lagi_mengubah_status_sendiri(): void
    {
        $this->siapkanPdf();

        $data = $this->data();

        $mail = new TukarFakturMail((string) $data->id, $this->filePath);
        unset($mail);
        gc_collect_cycles();

        $this->assertSame(TukarFakturStatus::Pending, $data->fresh()->status);
    }
}
