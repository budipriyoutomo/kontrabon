<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TukarFaktur extends Model
{
    use HasUuids;

    protected $table = 'tukar_fakturs';

    protected $fillable = [
        'pt_tujuan',
        'perusahaan_id',
        'perusahaan_pengaju',
        'tanggal_tukar',
        'no_kwitansi',
        'jumlah_rupiah',
        'nama_pic',
        'email_penerima', 
        'tanggal_pembayaran',
        'status',
    ];

    protected $dates = [
        'tanggal_tukar',
        'tanggal_pembayaran',
    ];

    /**
     * No kwitansi selalu disimpan dalam huruf kapital.
     */
    public function setNoKwitansiAttribute($value): void
    {
        $this->attributes['no_kwitansi'] = $value === null
            ? null
            : strtoupper(trim($value));
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id');
    }

}
