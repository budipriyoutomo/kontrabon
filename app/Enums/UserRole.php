<?php

namespace App\Enums;

use Illuminate\Support\Facades\Route;

/**
 * Peran pengguna aplikasi finance.
 *
 * Alur kerja tukar faktur: kontrabon input data dan mengisi tanggal
 * pembayaran (memicu email), verifikator memeriksa data yang emailnya sudah
 * terkirim, lalu billing memproses pembayarannya.
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Kontrabon = 'kontrabon';
    case Verifikator = 'verifikator';
    case Billing = 'billing';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Kontrabon => 'Kontrabon',
            self::Verifikator => 'Verifikator',
            self::Billing => 'Billing',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Admin => 'Akses penuh, termasuk manajemen pengguna.',
            self::Kontrabon => 'Input data tukar faktur dan pengisian tanggal pembayaran.',
            self::Verifikator => 'Verifikasi data tukar faktur yang emailnya sudah terkirim.',
            self::Billing => 'Rekap pembayaran atas data yang sudah terverifikasi.',
        };
    }

    /**
     * Halaman utama tiap peran setelah login.
     *
     * Modul verifikasi dan billing belum dibuat, jadi rute yang belum
     * terdaftar otomatis jatuh ke dashboard supaya login tidak error.
     */
    public function homeRouteName(): string
    {
        $route = match ($this) {
            self::Admin, self::Kontrabon => 'admin.tukar-faktur.index',
            self::Verifikator => 'admin.verifikasi.index',
            self::Billing => 'billing.index',
        };

        return Route::has($route) ? $route : 'dashboard';
    }

    /** @return array<string, string> value => label */
    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            fn (array $carry, self $role) => $carry + [$role->value => $role->label()],
            []
        );
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
