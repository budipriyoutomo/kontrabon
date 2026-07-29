<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Perusahaan extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'perusahaans';

    protected $fillable = [
        'kode',
        'nama',
        'npwp',
        'top',
        'alamat',
        'telepon',
        'email',
        'nama_pic',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'top' => 'integer',
    ];

    public function tukarFakturs()
    {
        return $this->hasMany(TukarFaktur::class, 'perusahaan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
