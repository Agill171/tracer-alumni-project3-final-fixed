<?php

use App\Http\Controllers\AccuracyAuditController;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\AutoEnrichmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HasilPelacakanController;
use App\Http\Controllers\PelacakanBatchController;
use App\Http\Controllers\PelacakanQueryController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;


/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');


/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/

Route::get(
    'dashboard',
    [
        DashboardController::class,
        'index',
    ]
)
    ->middleware([
        'auth',
        'verified',
    ])
    ->name('dashboard');


/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'verified',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | SETTINGS
    |--------------------------------------------------------------------------
    */

    Route::redirect(
        'settings',
        'settings/profile'
    );


    Volt::route(
        'settings/profile',
        'settings.profile'
    )->name(
        'settings.profile'
    );


    Volt::route(
        'settings/password',
        'settings.password'
    )->name(
        'settings.password'
    );


    Volt::route(
        'settings/appearance',
        'settings.appearance'
    )->name(
        'settings.appearance'
    );


    /*
    |--------------------------------------------------------------------------
    | IMPORT / EXPORT ALUMNI
    |--------------------------------------------------------------------------
    */

    Route::get(
        'alumni/import/excel',
        [
            AlumniController::class,
            'importForm',
        ]
    )->name(
        'alumni.import.form'
    );


    Route::post(
        'alumni/import/excel',
        [
            AlumniController::class,
            'importExcel',
        ]
    )->name(
        'alumni.import.excel'
    );


    Route::get(
        'alumni/export/excel',
        [
            AlumniController::class,
            'exportExcel',
        ]
    )->name(
        'alumni.export.excel'
    );


    Route::get(
        'alumni/export/download',
        [
            AlumniController::class,
            'downloadExport',
        ]
    )->name(
        'alumni.export.download'
    );


    /*
    |--------------------------------------------------------------------------
    | BATCH PELACAKAN
    |--------------------------------------------------------------------------
    */

    Route::get(
        'pelacakan-batches',
        [
            PelacakanBatchController::class,
            'index',
        ]
    )->name(
        'pelacakan-batches.index'
    );


    Route::post(
        'pelacakan-batches',
        [
            PelacakanBatchController::class,
            'store',
        ]
    )->name(
        'pelacakan-batches.store'
    );


    /*
    |--------------------------------------------------------------------------
    | AUTO ENRICHMENT PROJECT 4
    |--------------------------------------------------------------------------
    |
    | start:
    | Menjalankan Auto Enrichment pada alumni di dalam batch.
    |
    | review:
    | Membuka evidence kandidat yang membutuhkan
    | pemeriksaan manual.
    |
    | reject:
    | Menolak kandidat Auto Enrichment sebagai false positive
    | tanpa menghapus jejak evidence.
    |
    */

    Route::post(
        'pelacakan-batches/{batch}/auto-enrichment',
        [
            AutoEnrichmentController::class,
            'start',
        ]
    )->name(
        'pelacakan-batches.enrichment.start'
    );


    Route::get(
        'pelacakan-batch-items/{item}/candidates',
        [
            AutoEnrichmentController::class,
            'review',
        ]
    )->name(
        'pelacakan-batches.enrichment.review'
    );


    Route::post(
        'pelacakan/{pelacakan}/reject-auto',
        [
            AutoEnrichmentController::class,
            'reject',
        ]
    )->name(
        'pelacakan.enrichment.reject'
    );


    /*
    |--------------------------------------------------------------------------
    | DETAIL BATCH
    |--------------------------------------------------------------------------
    */

    Route::get(
        'pelacakan-batches/{batch}',
        [
            PelacakanBatchController::class,
            'show',
        ]
    )->name(
        'pelacakan-batches.show'
    );


    /*
    |--------------------------------------------------------------------------
    | QUERY PELACAKAN
    |--------------------------------------------------------------------------
    */

    Route::post(
        'alumni/{alumni}/query-pelacakan',
        [
            PelacakanQueryController::class,
            'store',
        ]
    )->name(
        'pelacakan.query.store'
    );


    /*
    |--------------------------------------------------------------------------
    | HASIL PELACAKAN / EVIDENCE
    |--------------------------------------------------------------------------
    */

    Route::get(
        'alumni/{alumni}/pelacakan/create',
        [
            HasilPelacakanController::class,
            'create',
        ]
    )->name(
        'pelacakan.create'
    );


    Route::post(
        'alumni/{alumni}/pelacakan',
        [
            HasilPelacakanController::class,
            'store',
        ]
    )->name(
        'pelacakan.store'
    );


    Route::get(
        'pelacakan/{pelacakan}/edit',
        [
            HasilPelacakanController::class,
            'edit',
        ]
    )->name(
        'pelacakan.edit'
    );


    Route::put(
        'pelacakan/{pelacakan}',
        [
            HasilPelacakanController::class,
            'update',
        ]
    )->name(
        'pelacakan.update'
    );


    Route::delete(
        'pelacakan/{pelacakan}',
        [
            HasilPelacakanController::class,
            'destroy',
        ]
    )->name(
        'pelacakan.destroy'
    );


    /*
    |--------------------------------------------------------------------------
    | ACCURACY AUDIT PROJECT 4
    |--------------------------------------------------------------------------
    */

    Route::get(
        'accuracy-audit',
        [
            AccuracyAuditController::class,
            'index',
        ]
    )->name(
        'accuracy-audit.index'
    );


    Route::put(
        'accuracy-audit/{sample}',
        [
            AccuracyAuditController::class,
            'update',
        ]
    )->name(
        'accuracy-audit.update'
    );


    Route::delete(
        'accuracy-audit/{sample}',
        [
            AccuracyAuditController::class,
            'reset',
        ]
    )->name(
        'accuracy-audit.reset'
    );


    /*
    |--------------------------------------------------------------------------
    | CRUD ALUMNI
    |--------------------------------------------------------------------------
    |
    | Diletakkan setelah route import/export dan pelacakan agar route
    | dinamis alumni/{alumni} tidak mengambil URL khusus di atas.
    |
    */

    Route::resource(
        'alumni',
        AlumniController::class
    )->parameters([
        'alumni' => 'alumni',
    ]);
});


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';