<?php

namespace App\Mail;

use App\Models\TukarFaktur;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Isi email bukti tukar faktur.
 *
 * Mailable ini sengaja TIDAK ShouldQueue: antreannya diurus
 * App\Jobs\KirimEmailTukarFaktur, yang juga bertanggung jawab menaikkan
 * status hanya bila pengirimannya benar-benar berhasil.
 */
class TukarFakturMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $tukarFakturId, // UUID
        public string $filePath
    ) {}

    public function build()
    {
        $data = TukarFaktur::find($this->tukarFakturId);

        if (! $data) {
            throw new RuntimeException('Data tukar faktur tidak ditemukan: ' . $this->tukarFakturId);
        }

        if (! Storage::exists($this->filePath)) {
            throw new RuntimeException('PDF tidak ditemukan: ' . $this->filePath);
        }

        return $this->subject('Bukti Faktur Online Maharasa Group')
            ->view('emails.tukar-faktur', compact('data'))
            ->attach(Storage::path($this->filePath), [
                'as' => 'Tukar-Faktur-' . $data->no_kwitansi . '.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}
