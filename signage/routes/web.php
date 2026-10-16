<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TokoController;
use App\Http\Controllers\DigitalSignageController;
use App\Http\Controllers\DigitalSignageDeviceController;
use App\Http\Controllers\FileIklanController;
use App\Http\Controllers\JadwalTayangController;
use App\Http\Controllers\DistribusiJadwalController;
use App\Http\Controllers\ModelSignageController;
use Illuminate\Support\Facades\Artisan;

Route::get('/', fn() => redirect()->route('login'));

// Jembatan Storage
Route::get('/link-storage', function () {
    Artisan::call('storage:link');
    return 'BERHASIL! Folder Storage sudah terbuka.';
});

// 🔐 Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 🔒 Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Panel Client (hanya superadmin)
    Route::middleware('superadmin')->prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('index');
        Route::get('/create', [ClientController::class, 'create'])->name('create');
        Route::post('/', [ClientController::class, 'store'])->name('store');
        Route::patch('/{client}/toggle', [ClientController::class, 'toggle'])->name('toggle');
    });
    
    // Data Master
    Route::resource('model-signage', ModelSignageController::class);
    Route::resource('toko', TokoController::class)->except(['show']);
    Route::resource('signage', DigitalSignageController::class)->except(['show']);
    
    // Devices
    Route::get('/devices/create', [DigitalSignageDeviceController::class, 'create'])->name('devices.create');
    Route::get('/devices', [DigitalSignageDeviceController::class, 'index'])->name('devices.index');
    Route::get('/devices/{device}/edit', [DigitalSignageDeviceController::class, 'edit'])->name('devices.edit');
    Route::put('/devices/{device}', [DigitalSignageDeviceController::class, 'update'])->name('devices.update');
    Route::delete('/devices/{device}', [DigitalSignageDeviceController::class, 'destroy'])->name('devices.destroy');

    // Iklan Modules
    Route::resource('file-iklan', FileIklanController::class)->except(['show']);
    Route::resource('jadwal', JadwalTayangController::class)->except(['show']);

    // Distribusi Jadwal - alur 2 langkah (Form jadwal -> Pilih lokasi tayang)
    Route::post('distribusi/lanjut', [DistribusiJadwalController::class, 'lanjut'])->name('distribusi.lanjut');
    Route::get('distribusi/lokasi', [DistribusiJadwalController::class, 'lokasi'])->name('distribusi.lokasi');
    Route::post('distribusi/lokasi', [DistribusiJadwalController::class, 'simpanLokasi'])->name('distribusi.lokasi.simpan');
    Route::get('distribusi/batal', [DistribusiJadwalController::class, 'batal'])->name('distribusi.batal');

    // 🎯 FIX: Distribusi Jadwal (Mapping Parameter agar ID terbaca di Controller)
    Route::resource('distribusi', DistribusiJadwalController::class, [
        'parameters' => [
            'distribusi' => 'distribusi'
        ]
    ])->except(['show']);

    // API
    Route::get('/api/device/playlist', [DigitalSignageDeviceController::class, 'getPlaylist'])->name('api.device.playlist');
    
    // Cache Clear
    Route::get('/bersihkan-aplikasi', function() {
        Artisan::call('view:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        return "✨ Cache Laravel Berhasil Dibersihkan Total!";
    });
});