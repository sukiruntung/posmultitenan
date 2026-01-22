<?php

use App\Http\Controllers\LaporanController;
use App\Http\Controllers\Reports\ReportController;
use App\Models\Accesses\MenuAccess;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Rawilk\Printing\Facades\Printing;

Route::get('/', function () {
    return view('welcome');
})->name('home');
Route::get('/laporan/excel', [LaporanController::class, 'exportExcel']);

// routes/web.php
Route::get('/test-cache', function () {
    $start = microtime(true);

    // --- A: ambil dari database ---
    $fromDb = MenuAccess::where('user_group_id', 2)->where('menu_id', 2)->first();

    $timeDb = microtime(true) - $start;
    echo "Dari DB: " . round($timeDb * 1000, 2) . " ms<br>";

    // --- B: ambil dari cache ---
    $start = microtime(true);

    $fromCache = Cache::get('menu_access:2:2');

    $timeCache = microtime(true) - $start;
    echo "Dari Cache: " . round($timeCache * 1000, 2) . " ms<br>";
});
Route::get('/test-print', function () {
    // Ambil daftar printer dari Windows
    $printers = Printing::printers();

    if ($printers->isEmpty()) {
        return "Tidak ada printer ditemukan.";
    }

    // Tampilkan semua printer
    foreach ($printers as $printer) {
        echo "ID: {$printer->id}, Nama: {$printer->name}<br>";
    }

    // Pilih printer pertama (atau sesuai ID/nama yang kamu mau)
    $printer = Printing::printer($printers->first()->id);

    // Kirim teks sederhana
    $printer->Print("Halo dari Laravel ke printer Bluetooth Ecpost!");

    return "Print job dikirim ke printer: " . $printers->first()->name;
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
