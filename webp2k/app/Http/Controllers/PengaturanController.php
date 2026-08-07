<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PengaturanController extends Controller
{
    public function indexContent()
    {
        // Pastikan path view ini benar: resources/views/kunjungan/partials/pengaturan_content.blade.php
        return view('kunjungan.partials.pengaturan_content');
    }

    public function updateAkun(Request $request)
    {
       /** @var \App\Models\Karyawan $user */
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
        /** @var \App\Models\Karyawan $user */
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
        /** @var \App\Models\Karyawan $user */
        $user = Auth::guard('karyawan')->user();

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            // Hapus foto lama (jika disimpan di folder uploads/avatars)
            if ($user->avatar && $user->avatar !== 'assets/avatar.png') {
                $pathAvatarLama = public_path($user->avatar);
                if (file_exists($pathAvatarLama) && is_file($pathAvatarLama)) {
                    @unlink($pathAvatarLama);
                }
            }

            // Simpan langsung ke public/uploads/avatars (bukan Storage disk).
            // Hindari Storage::disk('public')->store() yang memakai file_put_contents
            // dan butuh symlink public/storage + storage/app/public yang sering
            // belum disiapkan di server -> menyebabkan error file_put_contents.
            $dirAvatar = public_path('uploads/avatars');
            if (!is_dir($dirAvatar)) {
                @mkdir($dirAvatar, 0775, true);
            }

            if (!is_writable($dirAvatar)) {
                return response()->json([
                    'error' => 'Folder upload tidak dapat ditulis. Periksa permission direktori uploads/avatars.'
                ], 500);
            }

            $extAvatar = strtolower($request->file('avatar')->getClientOriginalExtension());
            $namaAvatar = 'avatar_' . time() . '_' . uniqid() . '.' . $extAvatar;

            $request->file('avatar')->move($dirAvatar, $namaAvatar);

            $path = 'uploads/avatars/' . $namaAvatar;

            // Update DB
            $user->update(['avatar' => $path]);

            return response()->json([
                'success' => 'Avatar berhasil diperbarui!',
                'url' => asset($path)
            ]);
        }
    }
}