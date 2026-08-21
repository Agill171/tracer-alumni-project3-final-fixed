<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelacakan_batches', function (Blueprint $table) {
            $table->unsignedInteger('enrichment_processed_items')
                ->default(0);

            $table->unsignedInteger('identified_items')
                ->default(0);

            $table->unsignedInteger('review_items')
                ->default(0);

            $table->unsignedInteger('not_found_items')
                ->default(0);

            $table->unsignedInteger('enrichment_failed_items')
                ->default(0);

            $table->timestamp('enrichment_started_at')
                ->nullable();

            $table->timestamp('enrichment_finished_at')
                ->nullable();
        });

        Schema::table('pelacakan_batch_items', function (Blueprint $table) {
            $table->string('enrichment_status', 50)
                ->nullable()
                ->index();

            $table->unsignedInteger('enrichment_attempts')
                ->default(0);

            $table->text('enrichment_error')
                ->nullable();

            $table->timestamp('enrichment_started_at')
                ->nullable();

            $table->timestamp('enrichment_finished_at')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pelacakan_batch_items', function (Blueprint $table) {
            $table->dropColumn([
                'enrichment_status',
                'enrichment_attempts',
                'enrichment_error',
                'enrichment_started_at',
                'enrichment_finished_at',
            ]);
        });

        Schema::table('pelacakan_batches', function (Blueprint $table) {
            $table->dropColumn([
                'enrichment_processed_items',
                'identified_items',
                'review_items',
                'not_found_items',
                'enrichment_failed_items',
                'enrichment_started_at',
                'enrichment_finished_at',
            ]);
        });
    }
};