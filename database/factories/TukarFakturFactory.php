<?php

namespace Database\Factories;

use App\Enums\TukarFakturStatus;
use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TukarFaktur>
 */
class TukarFakturFactory extends Factory
{
    public function definition(): array
    {
        $perusahaan = Perusahaan::factory();

        return [
            'pt_tujuan' => fake()->randomElement([
                'PT Panca Abadi Nan Jaya (Sushi Tei & Tom Sushi)',
                'PT Maharasa Jaya Abadi (Pepper Lunch)',
                'PT Loka Abadi Nanjaya (Waruna)',
                'PT Sukha Abadi Nanjaya (Song Fa)',
            ]),
            'perusahaan_id' => $perusahaan,
            'perusahaan_pengaju' => fn (array $attributes) => Perusahaan::find($attributes['perusahaan_id'])?->nama
                ?? fake()->company(),
            'tanggal_tukar' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'no_kwitansi' => 'KW-' . fake()->unique()->numerify('######'),
            'jumlah_rupiah' => fake()->numberBetween(500_000, 50_000_000),
            'nama_pic' => fake()->name(),
            'email_penerima' => fake()->safeEmail(),
            'tanggal_pembayaran' => null,
            'status' => TukarFakturStatus::Pending,
        ];
    }

    public function status(TukarFakturStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    /** Menunggu verifikasi: email sudah terkirim, tanggal bayar sudah diisi. */
    public function emailSent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TukarFakturStatus::EmailSent,
            'tanggal_pembayaran' => now()->addDays(30)->toDateString(),
        ]);
    }

    public function verified(?User $verifikator = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TukarFakturStatus::Verified,
            'tanggal_pembayaran' => now()->addDays(30)->toDateString(),
            'verified_at' => now(),
            'verified_by' => $verifikator?->id ?? User::factory(),
        ]);
    }
}
