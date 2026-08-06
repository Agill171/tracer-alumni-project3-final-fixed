<?php

use App\Models\Alumni;
use App\Models\HasilPelacakan;
use App\Models\User;

it('generates and stores auditable public search queries', function () {
    $user = User::factory()->create();
    $alumni = Alumni::factory()->create([
        'nama' => 'Muh Wahyudi Akil Somar',
        'prodi' => 'Informatika',
        'tahun_lulus' => 2024,
    ]);

    $response = $this->actingAs($user)->post(route('pelacakan.query.store', $alumni), [
        'sources' => ['google', 'linkedin', 'github'],
    ]);

    $response->assertRedirect(route('alumni.show', $alumni));
    $this->assertDatabaseCount('pelacakan_queries', 3);
    $this->assertDatabaseHas('pelacakan_queries', [
        'alumni_id' => $alumni->id,
        'sumber' => 'linkedin',
        'status' => 'Disiapkan',
    ]);
});

it('calculates confidence score and updates alumni status', function () {
    $user = User::factory()->create();
    $alumni = Alumni::factory()->create();

    $response = $this->actingAs($user)->post(route('pelacakan.store', $alumni), [
        'status_pelacakan' => Alumni::STATUS_TERIDENTIFIKASI,
        'judul_temuan' => 'Profil LinkedIn sesuai',
        'sumber_temuan' => 'LinkedIn',
        'link_bukti' => 'https://www.linkedin.com/in/example',
        'tanggal_ditemukan' => now()->toDateString(),
        'cocok_nama' => 1,
        'cocok_afiliasi' => 1,
        'cocok_timeline' => 1,
        'cocok_bidang' => 1,
    ]);

    $response->assertRedirect(route('alumni.show', $alumni));
    $this->assertDatabaseHas('hasil_pelacakans', [
        'alumni_id' => $alumni->id,
        'confidence_score' => 100,
        'kategori_kecocokan' => HasilPelacakan::KATEGORI_KUAT,
    ]);
    expect($alumni->fresh()->status_verifikasi)->toBe(Alumni::STATUS_TERIDENTIFIKASI);
});

it('classifies a partial match as needing verification', function () {
    expect(HasilPelacakan::classify(65))->toBe(HasilPelacakan::KATEGORI_VERIFIKASI)
        ->and(HasilPelacakan::classify(40))->toBe(HasilPelacakan::KATEGORI_TIDAK_COCOK)
        ->and(HasilPelacakan::classify(90))->toBe(HasilPelacakan::KATEGORI_KUAT);
});
