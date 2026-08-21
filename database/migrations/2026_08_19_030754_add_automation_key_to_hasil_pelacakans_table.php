<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hasil_pelacakans', function (Blueprint $table) {
            $table->string(
                'automation_key',
                64
            )
                ->nullable()
                ->unique();
        });
    }

    public function down(): void
    {
        Schema::table('hasil_pelacakans', function (Blueprint $table) {
            $table->dropColumn(
                'automation_key'
            );
        });
    }
};