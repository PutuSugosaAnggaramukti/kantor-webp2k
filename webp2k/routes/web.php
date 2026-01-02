<?php

use Illuminate\Http\Request;
use App\Http\Controllers\dashboard\DashboardController;

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
