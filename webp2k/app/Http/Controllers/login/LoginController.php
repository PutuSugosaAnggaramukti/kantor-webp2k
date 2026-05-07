<?php

namespace App\Http\Controllers\login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

   public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        $throttleKey = $request->ip() . '|' . $credentials['username'];

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->with('error', "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.");
        }

        // 1. LOGIN ADMIN
        if (Auth::guard('web')->attempt([
            'name' => $credentials['username'],
            'password' => $credentials['password']
        ])) {
            RateLimiter::clear($throttleKey); // reset jika sukses

            $request->session()->regenerate();
            return redirect()->intended('/admin/dashboard');
        }

        // 2. LOGIN KARYAWAN
        if (Auth::guard('karyawan')->attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password']
        ])) {
            RateLimiter::clear($throttleKey); 

            $request->session()->regenerate();
            return redirect()->intended('/user/dashboard');
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withInput()->with('error', 'Username atau password salah');
    }

    public function logout(Request $request)
    {
        if (Auth::guard('karyawan')->check()) {
            Auth::guard('karyawan')->logout();
        } elseif (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Anda telah berhasil keluar.');
    }
}
