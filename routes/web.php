<?php

use App\Http\Controllers\AlumniController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HasilPelacakanController;
use App\Http\Controllers\PelacakanQueryController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    Route::get('alumni/import/excel', [AlumniController::class, 'importForm'])
        ->name('alumni.import.form');

    Route::post('alumni/import/excel', [AlumniController::class, 'importExcel'])
        ->name('alumni.import.excel');

    Route::get('alumni/export/excel', [AlumniController::class, 'exportExcel'])
        ->name('alumni.export.excel');

    Route::post('alumni/{alumni}/query-pelacakan', [PelacakanQueryController::class, 'store'])
        ->name('pelacakan.query.store');

    Route::get('alumni/{alumni}/pelacakan/create', [HasilPelacakanController::class, 'create'])
        ->name('pelacakan.create');

    Route::post('alumni/{alumni}/pelacakan', [HasilPelacakanController::class, 'store'])
        ->name('pelacakan.store');

    Route::get('pelacakan/{pelacakan}/edit', [HasilPelacakanController::class, 'edit'])
        ->name('pelacakan.edit');

    Route::put('pelacakan/{pelacakan}', [HasilPelacakanController::class, 'update'])
        ->name('pelacakan.update');

    Route::delete('pelacakan/{pelacakan}', [HasilPelacakanController::class, 'destroy'])
        ->name('pelacakan.destroy');

    Route::resource('alumni', AlumniController::class)
        ->parameters(['alumni' => 'alumni']);
});

require __DIR__.'/auth.php';