<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hasil_pelacakans', function (Blueprint $table) {
            $table->string('status_audit')
                ->default('Belum Diaudit')
                ->after('sinyal_identitas');

            $table->text('catatan_audit')
                ->nullable()
                ->after('status_audit');

            $table->foreignId('audited_by')
                ->nullable()
                ->after('catatan_audit')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('audited_at')
                ->nullable()
                ->after('audited_by');

            $table->index('status_audit');
            $table->index('audited_at');
        });
    }

    public function down(): void
    {
        Schema::table('hasil_pelacakans', function (Blueprint $table) {
            $table->dropForeign(['audited_by']);

            $table->dropIndex(['status_audit']);
            $table->dropIndex(['audited_at']);

            $table->dropColumn([
                'status_audit',
                'catatan_audit',
                'audited_by',
                'audited_at',
            ]);
        });
    }
};