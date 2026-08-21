<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelacakan_kandidats', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pelacakan_batch_id')
                ->constrained('pelacakan_batches')
                ->cascadeOnDelete();

            $table->foreignId('pelacakan_batch_item_id')
                ->constrained('pelacakan_batch_items')
                ->cascadeOnDelete();

            $table->foreignId('alumni_id')
                ->constrained('alumnis')
                ->cascadeOnDelete();

            $table->foreignId('pelacakan_query_id')
                ->nullable()
                ->constrained('pelacakan_queries')
                ->nullOnDelete();

            $table->string('provider', 50);

            $table->string('source_key', 50)
                ->nullable();

            $table->unsignedSmallInteger('rank')
                ->default(1);

            $table->string('title')
                ->nullable();

            $table->text('url');

            $table->string('url_hash', 64);

            $table->text('snippet')
                ->nullable();

            $table->string('domain')
                ->nullable();

            $table->json('identity_signals')
                ->nullable();

            $table->unsignedTinyInteger('base_confidence_score')
                ->default(0);

            $table->unsignedTinyInteger('confidence_score')
                ->default(0);

            $table->string('kategori_kecocokan', 50)
                ->nullable();

            $table->json('project4_candidates')
                ->nullable();

            $table->string('status', 50)
                ->default('Kandidat');

            $table->timestamp('searched_at')
                ->nullable();

            $table->timestamps();

            $table->unique(
                [
                    'pelacakan_batch_item_id',
                    'pelacakan_query_id',
                    'url_hash',
                ],
                'pelacakan_kandidat_unique'
            );

            $table->index([
                'alumni_id',
                'confidence_score',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'pelacakan_kandidats'
        );
    }
};