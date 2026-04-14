<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PengaturanAdmController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return view('admin.partials.pengaturan');
        }

        // Saat refresh (bukan AJAX), kita kirim variabel $content agar Blade tidak menampilkan tabel karyawan
        return view('admin.datakaryawan', [
            'content' => view('admin.partials.pengaturan')->render()
        ]);
    }

   public function updateSandi(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false, 
                'message' => 'Kata sandi saat ini salah.'
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true, 
            'message' => 'Kata sandi admin berhasil diperbarui.'
        ]);
    }
}