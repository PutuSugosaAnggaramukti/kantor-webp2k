<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\login\LoginController;
use App\Http\Controllers\dashboard\DashboardController;
use App\Http\Controllers\dashboard\DashboardAdminController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\KunjunganController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\karyawan\KaryawanController;
use App\Http\Controllers\karyawan\AdmKunjunganController;
use App\Http\Controllers\karyawan\AdmDokumenController;
use App\Http\Controllers\karyawan\NasabahController;
use App\Http\Controllers\karyawan\PelaporanController;

/*
|--------------------------------------------------------------------------
| LOGIN PREVIEW (DEMO MODE)
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Grouping untuk Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardAdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin', [KaryawanController::class, 'index'])->name('karyawan.index');
    Route::get('/data-karyawan-content', [KaryawanController::class, 'getContent']);
    Route::post('/karyawan/store', [KaryawanController::class, 'store'])->name('karyawan.store');
    Route::get('/adm-kunjungan-content', [AdmKunjunganController::class, 'index'])->name('admin.kunjungan.index');
    Route::get('/data-kunjungan-content', [AdmKunjunganController::class, 'dataKunjunganContent'])->name('admin.kunjungan.rekap');
    Route::get('/kunjungan-detail/{kode_ao}-content', [AdmKunjunganController::class, 'detail'])
    ->where('kode_ao', '.*');
    Route::get('/detail-kunjungan-content', [AdmKunjunganController::class, 'detail']);
    Route::get('/nasabah-content', [NasabahController::class, 'nasabahContent']);
    Route::get('/nasabah-detail/{no_angsuran}-content', [NasabahController::class, 'detail']);
    Route::get('/pelaporan-content', [PelaporanController::class, 'index'])->name('pelaporan.content');
    Route::get('/pelaporan-detail/{id_ao}-content', [PelaporanController::class, 'detailAo'])->name('pelaporan.detail');
    Route::get('/detail-pelaporan-nasabah-content', [PelaporanController::class, 'detail_nasabah']);
    Route::get('/dokumen-content', [AdmDokumenController::class, 'dokumenIndex']);
    Route::get('/download-docx/{id}', [AdmDokumenController::class, 'downloadWord'])->name('download.docx');
    Route::get('/kunjungan-detail/{kode_ao}-content', [AdmKunjunganController::class, 'detail']);
    Route::post('/datakunjungan/store', [AdmKunjunganController::class, 'store'])->name('admin.datakunjungan.store');
    Route::get('/get-karyawan-list', [KaryawanController::class, 'getList'])->name('admin.karyawan.list');
});


// Grouping untuk User
Route::middleware(['auth:karyawan', 'role:user'])->prefix('user')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('user.dashboard');
    Route::get('/data-kunjungan', [KunjunganController::class, 'index'])->name('data-kunjungan');
    Route::get('/data-kunjungan-content', [KunjunganController::class, 'dataKunjunganContent'])->name('data.kunjungan');
    Route::get('/laporan-kunjungan-content', [KunjunganController::class, 'indexpelaporan'])->name('user.laporan.content');
    Route::get('/detail-pelaporan', [KunjunganController::class, 'detailPelaporan']);
    Route::get('/dokumen-content', [DokumenController::class, 'dokumenContent'])->name('dokumen.content');
    Route::get('/kunjungan/detail/{id}', [KunjunganController::class, 'showBukti'])->name('kunjungan.bukti');
    Route::post('/kunjungan/store', [KunjunganController::class, 'store'])->name('kunjungan.store');
    Route::get('/pengaturan-content', [PengaturanController::class, 'indexContent'])->name('pengaturan.content');
    Route::post('/pengaturan/update-akun', [PengaturanController::class, 'updateAkun'])->name('settings.akun');
    Route::post('/pengaturan/update-sandi', [PengaturanController::class, 'updateSandi'])->name('settings.sandi');
    Route::post('/pengaturan/upload-avatar', [PengaturanController::class, 'uploadAvatar'])->name('settings.avatar');
    Route::get('/export-pdf/{id}', [KunjunganController::class, 'exportPDF'])->name('export.pdf');
    Route::get('/export-word/{id}', [KunjunganController::class, 'exportWord'])->name('export.word');
});





