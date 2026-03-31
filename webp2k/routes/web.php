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
    // 1. DASHBOARD
    Route::get('/dashboard', [DashboardAdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard-detail/{type}', [DashboardAdminController::class, 'getDetail'])->name('admin.dashboard.detail');

    // 2. DATA KARYAWAN (Sudah benar)
    Route::get('/data-karyawan', [KaryawanController::class, 'index'])->name('karyawan.index');
    Route::get('/data-karyawan-content', [KaryawanController::class, 'getContent']);
    Route::get('/karyawan/{id}/edit', [KaryawanController::class, 'edit']);
    Route::put('/karyawan/{id}', [KaryawanController::class, 'update'])->name('karyawan.update');
    Route::post('/karyawan/store', [KaryawanController::class, 'store'])->name('karyawan.store');
    Route::get('/karyawan/{id}', [KaryawanController::class, 'show']);
    Route::get('/get-karyawan-list', [KaryawanController::class, 'getList'])->name('admin.karyawan.list');

    // 3. ADM KUNJUNGAN (Dibenahi rutenya agar tidak 404 saat refresh)
    Route::get('/data-kunjungan', [AdmKunjunganController::class, 'dataKunjunganContent'])->name('admin.kunjungan.index'); // Rute bersih
    Route::get('/data-kunjungan-content', [AdmKunjunganController::class, 'dataKunjunganContent']); 
    Route::get('/adm-kunjungan', [AdmKunjunganController::class, 'index'])->name('admin.adm-kunjungan.index');
    Route::get('/adm-kunjungan-content', [AdmKunjunganController::class, 'index'])->name('admin.kunjungan.input'); 
    Route::get('/kunjungan-detail/{kode_ao}', [AdmKunjunganController::class, 'detail'])->where('kode_ao', '.*');
    Route::post('/datakunjungan/store', [AdmKunjunganController::class, 'store'])->name('admin.datakunjungan.store');
    Route::post('/datakunjungan/import', [AdmKunjunganController::class, 'importExcel'])->name('admin.datakunjungan.import');
    Route::get('/kunjungan/export', [AdmKunjunganController::class, 'exportExcel'])->name('admin.kunjungan.export');

    // 4. DATA NASABAH (Dibenahi rutenya)
    Route::get('/nasabah', [NasabahController::class, 'nasabahContent'])->name('admin.nasabah.index'); // Rute bersih
    Route::get('/nasabah-content', [NasabahController::class, 'nasabahContent']);
    Route::post('/nasabah/store', [NasabahController::class, 'store'])->name('nasabah.store');
    Route::get('/get-daftar-no-anggota', [NasabahController::class, 'getDaftarNoAnggota']);
    Route::get('/get-nasabah/{no_angsuran}', [NasabahController::class, 'getNasabah']);
    Route::get('/nasabah-detail/{no_angsuran}', [NasabahController::class, 'detail']);
    Route::get('/nasabah/filter', [NasabahController::class, 'nasabahContent'])->name('admin.nasabah.filter');
    Route::get('/nasabah/export', [NasabahController::class, 'exportExcel'])->name('admin.nasabah.export');
    Route::post('/nasabah/import', [NasabahController::class, 'importExcel'])->name('admin.nasabah.import');
    Route::post('/nasabah/import-hb', [NasabahController::class, 'import_hb'])->name('admin.nasabah.import_hb');

    // 5. PELAPORAN (Dibenahi rutenya)
    Route::get('/pelaporan', [PelaporanController::class, 'index'])->name('pelaporan.index'); // Rute bersih
    Route::get('/pelaporan-content', [PelaporanController::class, 'index']);
    Route::get('/pelaporan-detail/{id_ao}', [PelaporanController::class, 'detailAo'])->name('pelaporan.detail');
    Route::get('/detail-pelaporan-nasabah', [PelaporanController::class, 'detail_nasabah']);
    Route::get('/pelaporan/export', [PelaporanController::class, 'exportExcel'])->name('admin.pelaporan.export');

    // 6. DOKUMEN (Dibenahi rutenya)
    Route::get('/dokumen', [AdmDokumenController::class, 'dokumenIndex'])->name('admin.dokumen.index'); // Rute bersih
    Route::get('/dokumen-content', [AdmDokumenController::class, 'dokumenIndex']);
    Route::get('/download-word/{id}', [AdmDokumenController::class, 'downloadWord'])->name('download.docx');
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
    Route::get('/export-excel', [KunjunganController::class, 'exportExcel'])->name('export.excel');
});





