<?php

namespace App\Mail;

use App\Models\TukarFaktur;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TukarFakturMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    public function __construct(
        public string $tukarFakturId, // UUID
        public string $filePath
    ) {}

   public function build()
    {
        $data = TukarFaktur::find($this->tukarFakturId);

        if (!$data) {
            throw new \RuntimeException('Data tidak ditemukan');
        }

        if (!Storage::exists($this->filePath)) {
            throw new \RuntimeException('PDF tidak ditemukan: ' . $this->filePath);
        }

        return $this->subject('Bukti Faktur Online Maharasa Group')
            ->view('emails.tukar-faktur', compact('data'))
            ->attach(Storage::path($this->filePath), [
                'as' => 'Tukar-Faktur-' . $data->no_kwitansi . '.pdf',
                'mime' => 'application/pdf',
            ]);
    }

    public function __destruct()
    {
        // Update status
        if ($data = TukarFaktur::find($this->tukarFakturId)) {
            if ($data->status !== 'email_sent') {
                $data->update(['status' => 'email_sent']);
            }
        }
  
    }
}
