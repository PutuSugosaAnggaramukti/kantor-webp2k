<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PengaturanController extends Controller
{
    public function indexContent()
    {
        // Pastikan path view ini benar: resources/views/kunjungan/partials/pengaturan_content.blade.php
        return view('kunjungan.partials.pengaturan_content');
    }

    public function updateAkun(Request $request)
    {
        $user = Auth::guard('karyawan')->user();

        // Validasi hanya kolom yang ada di model Karyawan
        $request->validate([
            'nama' => 'required|string|max:255'
        ]);

        $user->update([
            'nama' => $request->nama
            // Jika no_hp tidak ada di database, jangan masukkan di sini
        ]);

        return response()->json(['success' => 'Profil berhasil diperbarui!']);
    }

    public function updateSandi(Request $request)
    {
        $user = Auth::guard('karyawan')->user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Kata sandi lama salah.'], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json(['success' => 'Kata sandi berhasil diubah!']);
    }

   public function uploadAvatar(Request $request)
    {
        $user = Auth::guard('karyawan')->user();

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            // Hapus foto lama
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            
            // Update DB
            $user->update(['avatar' => $path]);

            return response()->json([
                'success' => 'Avatar berhasil diperbarui!', // Tambahkan ini
                'url' => asset('storage/' . $path)
            ]);
        }
    }
}