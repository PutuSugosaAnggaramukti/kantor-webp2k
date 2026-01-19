<?php

namespace App\Http\Controllers\login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'username' => ['required'], // Menyesuaikan input dari form
            'password' => ['required'],
        ], [
            'username.required' => 'Username wajib diisi',
            'password.required' => 'Password wajib diisi',
        ]);
    
        // Kita ubah 'username' menjadi 'name' karena di tabel users bawaan Laravel kolomnya bernama 'name'
        $loginData = [
            'name'     => $credentials['username'],
            'password' => $credentials['password']
        ];

        if (Auth::attempt($loginData)) {
            $request->session()->regenerate();
            $user = Auth::user();
        
            if ($user->role === 'admin') {
                // Pastikan mengarah ke /admin/dashboard sesuai prefix admin
                return redirect()->intended('/admin/dashboard')
                    ->with('success', 'Selamat datang Admin!');
            } else {
                // Pastikan mengarah ke /user/dashboard sesuai prefix user
                return redirect()->intended('/user/dashboard')
                    ->with('success', 'Login berhasil, selamat bertugas!');
            }
        }
    
        // Jika gagal
        return back()
            ->withInput()
            ->with('error', 'Username atau password salah');
    }

   public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah berhasil keluar.');
    }
}
