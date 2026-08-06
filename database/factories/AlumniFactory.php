<?php

namespace Database\Factories;

use App\Models\Alumni;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alumni>
 */
class AlumniFactory extends Factory
{
    protected $model = Alumni::class;

    public function definition(): array
    {
        $year = fake()->numberBetween(2015, (int) now()->format('Y'));

        return [
            'nama' => fake()->name(),
            'nim' => fake()->unique()->numerify('2020#########'),
            'prodi' => fake()->randomElement(['Informatika', 'Sistem Informasi', 'Manajemen']),
            'angkatan' => (string) max(1900, $year - 4),
            'tahun_lulus' => $year,
            'email' => fake()->unique()->safeEmail(),
            'no_hp' => fake()->numerify('08##########'),
            'tempat_bekerja' => fake()->company(),
            'posisi' => fake()->jobTitle(),
            'kategori_pekerjaan' => Alumni::KATEGORI_SWASTA,
            'status_verifikasi' => Alumni::STATUS_BELUM_DILACAK,
        ];
    }
}
