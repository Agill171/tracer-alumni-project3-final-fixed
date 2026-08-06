<?php

use App\Models\Alumni;
use App\Services\PelacakanQueryService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('pelacakan:siapkan-query {--days=30 : Buat ulang query jika query terakhir lebih lama dari jumlah hari ini}', function () {
    $days = max(1, (int) $this->option('days'));
    $cutoff = now()->subDays($days);
    $service = app(PelacakanQueryService::class);
    $processed = 0;

    Alumni::query()
        ->where(function ($query) use ($cutoff) {
            $query
                ->whereDoesntHave('queryPelacakans')
                ->orWhereDoesntHave('queryPelacakans', fn ($subQuery) => $subQuery->where('generated_at', '>=', $cutoff));
        })
        ->chunkById(100, function ($alumnis) use ($service, &$processed) {
            foreach ($alumnis as $alumni) {
                $service->generate($alumni);
                $processed++;
            }
        });

    $this->info("Query pelacakan disiapkan untuk {$processed} alumni.");
})->purpose('Menyiapkan tautan pencarian alumni secara berkala.');

Schedule::command('pelacakan:siapkan-query --days=30')
    ->dailyAt('01:00')
    ->withoutOverlapping();
