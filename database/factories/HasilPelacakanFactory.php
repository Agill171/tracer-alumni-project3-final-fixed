<?php

namespace Database\Factories;

use App\Models\Alumni;
use App\Models\HasilPelacakan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HasilPelacakan>
 */
class HasilPelacakanFactory extends Factory
{
    protected $model = HasilPelacakan::class;

    public function definition(): array
    {
        return [
            'alumni_id' => Alumni::factory(),
            'user_id' => User::factory(),
            'status_pelacakan' => Alumni::STATUS_PERLU_VERIFIKASI,
            'judul_temuan' => 'Profil kandidat alumni',
            'sumber_temuan' => 'LinkedIn',
            'link_bukti' => 'https://www.linkedin.com/in/example',
            'ringkasan_hasil' => fake()->sentence(),
            'tanggal_ditemukan' => now()->toDateString(),
            'confidence_score' => 65,
            'kategori_kecocokan' => HasilPelacakan::KATEGORI_VERIFIKASI,
            'sinyal_identitas' => [
                'nama' => true,
                'afiliasi' => true,
                'timeline' => false,
                'bidang' => false,
            ],
        ];
    }
}
