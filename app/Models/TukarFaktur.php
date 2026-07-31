<?php

namespace App\Models;

use App\Enums\TukarFakturStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TukarFaktur extends Model
{
    use HasFactory, HasUuids;

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
        'verified_at',
        'verified_by',
        'verified_note',
        'billed_at',
        'billed_by',
    ];

    protected $dates = [
        'tanggal_tukar',
        'tanggal_pembayaran',
    ];

    protected $casts = [
        'status' => TukarFakturStatus::class,
        'verified_at' => 'datetime',
        'billed_at' => 'datetime',
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

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function biller()
    {
        return $this->belongsTo(User::class, 'billed_by');
    }

    /** Menunggu diverifikasi: emailnya sudah terkirim ke supplier. */
    public function scopeMenungguVerifikasi($query)
    {
        return $query->where('status', TukarFakturStatus::EmailSent);
    }

    /** Sudah lolos verifikasi — sumber data modul billing. */
    public function scopeTerverifikasi($query)
    {
        return $query->whereIn('status', [
            TukarFakturStatus::Verified,
            TukarFakturStatus::Billing,
        ]);
    }

    /**
     * Tandai data sebagai terverifikasi.
     *
     * Pemanggil wajib memastikan transisinya sah lebih dulu
     * (lihat TukarFakturStatus::canTransitionTo).
     */
    public function tandaiTerverifikasi(User $verifikator, ?string $catatan = null): void
    {
        $this->update([
            'status' => TukarFakturStatus::Verified,
            'verified_at' => now(),
            'verified_by' => $verifikator->id,
            'verified_note' => $catatan,
        ]);
    }

    /**
     * Tandai sudah masuk proses billing.
     *
     * Pemanggil wajib memastikan transisinya sah lebih dulu
     * (lihat TukarFakturStatus::canTransitionTo).
     */
    public function tandaiMasukBilling(User $petugas): void
    {
        $this->update([
            'status' => TukarFakturStatus::Billing,
            'billed_at' => now(),
            'billed_by' => $petugas->id,
        ]);
    }

    /** Siap diproses billing: sudah terverifikasi, belum masuk billing. */
    public function scopeSiapBilling($query)
    {
        return $query->where('status', TukarFakturStatus::Verified);
    }
}
