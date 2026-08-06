<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelacakan_queries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_id')->constrained('alumnis')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sumber', 50);
            $table->unsignedTinyInteger('prioritas')->default(99);
            $table->text('query');
            $table->char('query_hash', 64);
            $table->text('url_pencarian');
            $table->string('status', 30)->default('Disiapkan');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['alumni_id', 'sumber', 'query_hash'], 'pelacakan_query_unique');
            $table->index(['alumni_id', 'generated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelacakan_queries');
    }
};
