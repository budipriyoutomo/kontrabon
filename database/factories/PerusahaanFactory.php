<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Perusahaan>
 */
class PerusahaanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode' => 'VND-' . fake()->unique()->numerify('####'),
            'nama' => 'PT ' . fake()->unique()->company(),
            'npwp' => fake()->numerify('##.###.###.#-###.###'),
            'top' => fake()->randomElement([14, 30, 45, 60]),
            'alamat' => fake()->address(),
            'telepon' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'nama_pic' => fake()->name(),
            'is_active' => true,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
