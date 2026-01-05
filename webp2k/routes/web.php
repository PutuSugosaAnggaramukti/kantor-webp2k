<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dashboard\DashboardController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\KunjunganController;
use App\Http\Controllers\PengaturanController;

/*
|--------------------------------------------------------------------------
| LOGIN PREVIEW (DEMO MODE)
|--------------------------------------------------------------------------
*/

// ✅ GET → tampilkan halaman login
Route::get('/login-preview', function () {
    return view('auth.login');
})->name('login.preview');

// ✅ POST → simulasi proses login
Route::post('/login-preview', function (Request $request) {

    $username = $request->input('username');
    $password = $request->input('password');

    // simulasi validasi kosong
    if (!$username || !$password) {
        return back()
            ->withInput()
            ->with('error', 'Username dan password wajib diisi');
    }

    // simulasi login salah
    if ($username !== 'admin' || $password !== 'admin') {
        return back()
            ->withInput()
            ->with('error', 'Username atau password salah (mode demo)');
    }

    // simulasi login sukses
    return back()->with('success', 'Login berhasil (mode demo)');
});

Route::get('/dashboard', [DashboardController::class, 'index']);

// Route Utama
Route::get('/data-kunjungan', [KunjunganController::class, 'index'])->name('data-kunjungan');

// Route untuk AJAX (Tanpa Refresh)
Route::get('/data-kunjungan-content', [KunjunganController::class, 'dataKunjunganContent'])->name('data.kunjungan');
Route::get('/laporan-kunjungan-content', [KunjunganController::class, 'laporanKunjunganContent'])->name('laporan.kunjungan');
Route::get('/dokumen-content', [DokumenController::class, 'dokumenContent'])->name('dokumen.content');

// Route untuk menampilkan halaman Bukti Kunjungan
Route::get('/kunjungan/detail/{id}', [KunjunganController::class, 'showBukti'])->name('kunjungan.bukti');

// Route untuk menyimpan data dari Form Kunjungan
Route::post('/kunjungan/store', [KunjunganController::class, 'store'])->name('kunjungan.store');

// Route untuk memanggil file partials/pengaturan_content.blade.php
Route::get('/pengaturan-content', [PengaturanController::class, 'indexContent'])->name('pengaturan.content');