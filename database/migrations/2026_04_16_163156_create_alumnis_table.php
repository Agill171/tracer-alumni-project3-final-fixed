<?php

use App\Models\Alumni;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumnis', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nim')->nullable()->unique();
            $table->string('prodi')->nullable();
            $table->string('angkatan', 4)->nullable();
            $table->unsignedSmallInteger('tahun_lulus')->nullable();

            $table->text('linkedin')->nullable();
            $table->text('instagram')->nullable();
            $table->text('facebook')->nullable();
            $table->text('tiktok')->nullable();

            $table->string('email')->nullable();
            $table->string('no_hp', 30)->nullable();

            $table->string('tempat_bekerja')->nullable();
            $table->text('alamat_bekerja')->nullable();
            $table->string('posisi')->nullable();
            $table->enum('kategori_pekerjaan', Alumni::kategoriPekerjaanOptions())->nullable();
            $table->text('sosmed_tempat_bekerja')->nullable();

            $table->enum('status_verifikasi', Alumni::statusOptions())
                ->default(Alumni::STATUS_BELUM_DILACAK);

            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['status_verifikasi', 'tahun_lulus']);
            $table->index(['nama', 'prodi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnis');
    }
};
