<?php

namespace App\Jobs;

use App\Enums\TukarFakturStatus;
use App\Mail\TukarFakturMail;
use App\Models\TukarFaktur;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * Kirim bukti tukar faktur ke supplier, lalu naikkan status pending →
 * email_sent.
 *
 * Status HANYA dinaikkan setelah server SMTP menerima pesannya. Selama
 * pengiriman belum berhasil, datanya tetap pending sehingga kontrabon bisa
 * mengulang dan verifikator tidak memverifikasi bukti yang belum sampai.
 */
class KirimEmailTukarFaktur implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    /** Jeda antar percobaan: SMTP yang sedang sibuk butuh waktu pulih. */
    public array $backoff = [60, 300];

    /**
     * @param  bool  $kirimUlang  Pengiriman ulang atas permintaan kontrabon.
     *                            Emailnya sudah pernah sampai, jadi status
     *                            tidak disentuh sama sekali — data yang sudah
     *                            verified/billing tidak boleh dimundurkan.
     */
    public function __construct(
        public string $tukarFakturId,
        public string $filePath,
        public bool $kirimUlang = false
    ) {}

    public function handle(): void
    {
        $data = TukarFaktur::find($this->tukarFakturId);

        if (! $data) {
            // Datanya sudah dihapus — tidak ada gunanya dicoba lagi.
            Log::warning('Email tukar faktur dilewati, data tidak ditemukan.', [
                'tukar_faktur_id' => $this->tukarFakturId,
            ]);

            return;
        }

        if (! $this->bolehDikirim($data)) {
            return;
        }

        if (! Storage::exists($this->filePath)) {
            throw new RuntimeException('PDF tukar faktur tidak ditemukan: ' . $this->filePath);
        }

        Mail::to($data->email_penerima)->send(
            new TukarFakturMail($this->tukarFakturId, $this->filePath)
        );

        if ($this->kirimUlang) {
            Log::info('Email tukar faktur berhasil dikirim ulang.', [
                'tukar_faktur_id' => $data->id,
                'tujuan' => $data->email_penerima,
            ]);

            return;
        }

        $data->refresh();

        if ($data->status->canTransitionTo(TukarFakturStatus::EmailSent)) {
            $data->update(['status' => TukarFakturStatus::EmailSent]);
        }
    }

    /**
     * Penjaga status, sekaligus pelindung dari job kembar bila tombolnya
     * tertekan dua kali.
     */
    private function bolehDikirim(TukarFaktur $data): bool
    {
        // Kirim ulang hanya masuk akal untuk data yang emailnya memang sudah
        // pernah dikirim; yang masih pending harus lewat alur normal.
        if ($this->kirimUlang) {
            if ($data->status === TukarFakturStatus::Pending) {
                Log::info('Kirim ulang dilewati, email pertama belum pernah dikirim.', [
                    'tukar_faktur_id' => $data->id,
                ]);

                return false;
            }

            return true;
        }

        if (! $data->status->canTransitionTo(TukarFakturStatus::EmailSent)) {
            Log::info('Email tukar faktur dilewati, status tidak lagi pending.', [
                'tukar_faktur_id' => $data->id,
                'status' => $data->status->value,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Dipanggil setelah percobaan terakhir habis. Statusnya sengaja dibiarkan
     * pending supaya kegagalan terlihat di daftar admin, bukan tersembunyi.
     */
    public function failed(?Throwable $e): void
    {
        Log::error('Email tukar faktur gagal terkirim.', [
            'tukar_faktur_id' => $this->tukarFakturId,
            'file' => $this->filePath,
            'error' => $e?->getMessage(),
        ]);
    }
}
