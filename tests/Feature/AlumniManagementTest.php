<?php

use App\Models\Alumni;
use App\Models\User;

it('redirects guests from alumni management to login', function () {
    $this->get(route('alumni.index'))->assertRedirect(route('login'));
});

it('allows an authenticated admin to create alumni data', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('alumni.store'), [
        'nama' => 'Muh Wahyudi Akil Somar',
        'nim' => '202010370311114',
        'prodi' => 'Informatika',
        'angkatan' => '2020',
        'tahun_lulus' => 2024,
        'email' => 'wahyudi@example.com',
        'kategori_pekerjaan' => Alumni::KATEGORI_SWASTA,
    ]);

    $response->assertRedirect(route('alumni.index'));
    $this->assertDatabaseHas('alumnis', [
        'nim' => '202010370311114',
        'status_verifikasi' => Alumni::STATUS_BELUM_DILACAK,
    ]);
});

it('rejects invalid alumni input', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->from(route('alumni.create'))
        ->post(route('alumni.store'), [
            'nama' => '',
            'email' => 'bukan-email',
            'kategori_pekerjaan' => 'Kategori Tidak Valid',
        ]);

    $response->assertRedirect(route('alumni.create'));
    $response->assertSessionHasErrors(['nama', 'email', 'kategori_pekerjaan']);
});

it('filters alumni by keyword and status', function () {
    $user = User::factory()->create();
    Alumni::factory()->create([
        'nama' => 'Alumni Ditemukan',
        'prodi' => 'Informatika',
        'status_verifikasi' => Alumni::STATUS_TERIDENTIFIKASI,
    ]);
    Alumni::factory()->create([
        'nama' => 'Alumni Lain',
        'prodi' => 'Manajemen',
        'status_verifikasi' => Alumni::STATUS_BELUM_DILACAK,
    ]);

    $this->actingAs($user)
        ->get(route('alumni.index', [
            'q' => 'Informatika',
            'status' => Alumni::STATUS_TERIDENTIFIKASI,
        ]))
        ->assertOk()
        ->assertSee('Alumni Ditemukan')
        ->assertDontSee('Alumni Lain');
});
