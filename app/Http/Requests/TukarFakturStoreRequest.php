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
            'perusahaan_id' => ['nullable', 'uuid', 'exists:perusahaans,id'],
            'perusahaan_pengaju' => [
                'required', 'string', 'max:255',
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
        $merge = [
            'no_kwitansi' => $this->no_kwitansi
                ? strtoupper(trim($this->no_kwitansi))
                : $this->no_kwitansi,
            'email_penerima' => $this->email_penerima
                ? strtolower(trim($this->email_penerima))
                : $this->email_penerima,
        ];

        // Nama perusahaan diambil dari master bila dipilih dari dropdown,
        // supaya penulisannya konsisten dan pengecekan duplikat akurat.
        if ($this->filled('perusahaan_id')) {
            $master = Perusahaan::find($this->perusahaan_id);

            if ($master) {
                $merge['perusahaan_pengaju'] = $master->nama;
            }
        } elseif ($this->filled('perusahaan_pengaju')) {
            $merge['perusahaan_pengaju'] = trim($this->perusahaan_pengaju);
        }

        $this->merge($merge);
    }
}
