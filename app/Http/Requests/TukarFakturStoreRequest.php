<?php

namespace App\Http\Requests;

use App\Models\Perusahaan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TukarFakturStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pt_tujuan' => ['required', 'string', 'max:255'],

            'perusahaan_pengaju' => [
                'required', 'string', 'max:255',
                // Harus cocok persis dengan master data perusahaan.
                fn ($attribute, $value, $fail) => $this->validasiNamaPerusahaan($value, $fail),
                // 1 perusahaan hanya boleh 1 pengajuan ke PT tujuan yang sama
                // pada tanggal tukar yang sama.
                Rule::unique('tukar_fakturs', 'perusahaan_pengaju')
                    ->where('pt_tujuan', $this->input('pt_tujuan'))
                    ->where('tanggal_tukar', $this->input('tanggal_tukar')),
            ],

            'tanggal_tukar' => ['required', 'date', 'after_or_equal:today'],
            'no_kwitansi' => ['required', 'string', 'max:100'],
            'jumlah_rupiah' => ['required', 'numeric', 'min:1'],
            'nama_pic' => ['required', 'string', 'max:255'],
            'email_penerima' => ['required', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'perusahaan_pengaju.unique' =>
                'Perusahaan ini sudah mengajukan tukar faktur ke PT tujuan tersebut '
                . 'pada tanggal yang sama. Satu perusahaan hanya boleh mengajukan '
                . 'satu kali per PT tujuan setiap tanggal.',
            'perusahaan_pengaju.required' => 'Nama perusahaan pengaju wajib diisi.',
        ];
    }

    public function attributes(): array
    {
        return [
            'pt_tujuan'          => 'PT tujuan',
            'perusahaan_pengaju' => 'perusahaan pengaju',
            'tanggal_tukar'      => 'tanggal tukar faktur',
            'no_kwitansi'        => 'no kwitansi',
            'jumlah_rupiah'      => 'jumlah rupiah',
            'nama_pic'           => 'nama PIC',
            'email_penerima'     => 'email penerima',
        ];
    }

    /**
     * Normalisasi sebelum validasi supaya aturan unique dicek terhadap
     * nilai yang sama persis dengan yang nanti disimpan.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'no_kwitansi' => $this->no_kwitansi
                ? strtoupper(trim($this->no_kwitansi))
                : $this->no_kwitansi,
            'email_penerima' => $this->email_penerima
                ? strtolower(trim($this->email_penerima))
                : $this->email_penerima,
            // Hanya spasi di ujung yang dibuang. Sisanya harus sama persis
            // dengan master, termasuk huruf besar/kecil.
            'perusahaan_pengaju' => $this->perusahaan_pengaju
                ? trim($this->perusahaan_pengaju)
                : $this->perusahaan_pengaju,
        ]);
    }

    /**
     * Nama perusahaan wajib terdaftar di master dan ditulis sama persis.
     *
     * Pencocokan sengaja tidak memakai LIKE: sebagian huruf tidak boleh
     * dianggap cocok, karena nama inilah yang tersimpan sebagai identitas
     * pengaju dan yang dipakai aturan unique.
     */
    private function validasiNamaPerusahaan($value, $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        // Ambil kandidat secara case-insensitive eksplisit (jangan bergantung
        // pada collation DB), lalu kecocokan persis ditentukan di PHP.
        $kandidat = Perusahaan::whereRaw('LOWER(nama) = ?', [mb_strtolower($value)])->get();

        $cocok = $kandidat->first(fn ($p) => $p->nama === $value);

        if ($cocok) {
            if (! $cocok->is_active) {
                $fail('Perusahaan "' . $cocok->nama . '" sedang tidak aktif. Hubungi finance Maharasa.');
            }

            return;
        }

        // Sampai di sini kandidat pasti hanya berbeda huruf besar/kecil,
        // karena pencarian di atas menyamakan LOWER(nama) dengan LOWER(input).
        // Hanya untuk kasus inilah penulisan yang benar boleh disarankan.
        $mirip = $kandidat->first();

        if ($mirip) {
            // Dititipkan ke session supaya form bisa menawarkan tombol
            // "Pakai penulisan ini" — pesan error sendiri hanya berupa teks.
            $this->session()->flash('saran_perusahaan', $mirip->nama);

            $fail(
                'Penulisan nama perusahaan belum tepat. Yang terdaftar: "' . $mirip->nama . '". '
                . 'Bedanya hanya pada huruf besar/kecil — perbaiki penulisannya lalu kirim ulang.'
            );

            return;
        }

        $fail('Nama perusahaan tidak terdaftar. Tulis sama persis seperti yang terdaftar di Maharasa, atau hubungi finance untuk pendaftaran.');
    }
}
