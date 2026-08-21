<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accuracy_audit_samples', function (Blueprint $table) {
            $table->id();

            $table->foreignId('alumni_id')
                ->constrained('alumnis')
                ->cascadeOnDelete();

            /*
             * Nomor sampel permanen 1 - 500.
             */
            $table->unsignedSmallInteger('sample_order');

            /*
             * Status audit:
             * - Belum Diaudit
             * - Benar
             * - Salah
             * - Perlu Verifikasi
             */
            $table->string('status_audit')
                ->default('Belum Diaudit');

            $table->text('catatan_audit')
                ->nullable();

            $table->foreignId('audited_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('audited_at')
                ->nullable();

            $table->timestamps();

            /*
             * Satu alumni hanya boleh masuk sampel sekali.
             */
            $table->unique('alumni_id');

            /*
             * Nomor sampel tidak boleh ganda.
             */
            $table->unique('sample_order');

            $table->index('status_audit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accuracy_audit_samples');
    }
};