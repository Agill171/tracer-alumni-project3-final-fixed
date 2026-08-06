<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_pelacakans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_id')->constrained('alumnis')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status_pelacakan');
            $table->string('judul_temuan')->nullable();
            $table->string('sumber_temuan')->nullable();
            $table->text('link_bukti')->nullable();
            $table->text('query_pencarian')->nullable();
            $table->text('ringkasan_hasil')->nullable();
            $table->date('tanggal_ditemukan')->nullable();
            $table->unsignedTinyInteger('confidence_score')->nullable();
            $table->string('kategori_kecocokan')->nullable();
            $table->json('sinyal_identitas')->nullable();
            $table->timestamps();

            $table->index(['alumni_id', 'tanggal_ditemukan']);
            $table->index(['status_pelacakan', 'kategori_kecocokan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_pelacakans');
    }
};
