<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelacakan_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('nama_batch', 150);

            $table->string('status', 30)
                ->default('Disiapkan');

            $table->unsignedInteger('total_items')
                ->default(0);

            $table->unsignedInteger('processed_items')
                ->default(0);

            $table->unsignedInteger('success_items')
                ->default(0);

            $table->unsignedInteger('failed_items')
                ->default(0);

            $table->json('sources')
                ->nullable();

            $table->timestamp('started_at')
                ->nullable();

            $table->timestamp('finished_at')
                ->nullable();

            $table->text('catatan')
                ->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });

        Schema::create('pelacakan_batch_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pelacakan_batch_id')
                ->constrained('pelacakan_batches')
                ->cascadeOnDelete();

            $table->foreignId('alumni_id')
                ->constrained('alumnis')
                ->cascadeOnDelete();

            $table->string('status', 30)
                ->default('Menunggu');

            $table->unsignedTinyInteger('attempts')
                ->default(0);

            $table->text('last_error')
                ->nullable();

            $table->timestamp('processed_at')
                ->nullable();

            $table->timestamps();

            $table->unique(
                ['pelacakan_batch_id', 'alumni_id'],
                'pelacakan_batch_alumni_unique'
            );

            $table->index(
                ['pelacakan_batch_id', 'status'],
                'pelacakan_batch_status_index'
            );

            $table->index(
                ['alumni_id', 'status'],
                'pelacakan_alumni_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelacakan_batch_items');
        Schema::dropIfExists('pelacakan_batches');
    }
};