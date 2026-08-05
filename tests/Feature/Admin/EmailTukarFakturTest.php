<?php

namespace Tests\Feature\Admin;

use App\Enums\TukarFakturStatus;
use App\Jobs\KirimEmailTukarFaktur;
use App\Mail\TukarFakturMail;
use App\Models\Perusahaan;
use App\Models\TukarFaktur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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
