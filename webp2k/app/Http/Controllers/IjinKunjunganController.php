<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IjinKunjungan; // Import Model
use Illuminate\Support\Facades\Auth; // Untuk ambil data login


class IjinKunjunganController extends Controller
{
    // 1. Menampilkan Halaman Form Ijin
    public function create()
    {
        return view('user.ijin.create'); // Sesuaikan dengan lokasi file blade kamu
    }

    // 2. Proses Simpan Data Ijin
    public function store(Request $request)
    {
        try {
            // 1. Validasi
            $request->validate([
                'tanggal' => 'required|date',
                'jenis_ijin' => 'required',
                'alasan' => 'required',
            ]);

            // 2. Cek User
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Sesi login habis.'], 401);
            }

            // 3. Simpan data ijin ke variabel $ijin
            $ijin = \App\Models\IjinKunjungan::create([
                'karyawan_id' => $user->id,
                'kode_ao' => $user->kode_ao,
                'tanggal' => $request->tanggal,
                'jenis_ijin' => $request->jenis_ijin,
                'alasan' => $request->alasan,
                'status' => 'pending' // Pastikan ada status default
            ]);

            // --- 4. TAMBAHKAN LOGIKA NOTIFIKASI DI SINI ---
            // Ambil semua user Admin
            $admins = \App\Models\User::all(); 

            $details = [
                'id_ijin' => $ijin->id,
                'nama_ao' => $user->nama, // Pastikan kolom 'nama' ada di tabel karyawan/user
                'pesan'   => "Mengajukan ijin " . $request->jenis_ijin,
                'status'  => 'pending'
            ];

            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\IjinKunjunganNotification($details));
            }
            // ----------------------------------------------

            return response()->json(['message' => 'Ijin berhasil dikirim']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

        // 1. Menampilkan semua pengajuan ijin ke dashboard Admin
  public function updateStatus(Request $request, $id)
{
    try {
        $ijin = IjinKunjungan::findOrFail($id);
        
        // Buat array data yang akan diupdate
        $updateData = [
            'status' => $request->status // 'disetujui' atau 'ditolak'
        ];

        // --- TAMBAHKAN LOGIKA INI ---
        // Jika ada input ao_pengganti dari request, masukkan ke array update
        if ($request->has('ao_pengganti') && $request->ao_pengganti != null) {
            $updateData['ao_pengganti'] = $request->ao_pengganti;
        }

        $ijin->update($updateData);
        // ----------------------------

        // Kirim notifikasi ke AO (kode kamu yang sudah ada)
        $karyawan = \App\Models\Karyawan::find($ijin->karyawan_id);
        if ($karyawan) {
            $details = [
                'id_ijin' => $ijin->id,
                'pesan'   => "Pengajuan ijin " . $ijin->jenis_ijin . " Anda telah " . $request->status,
                'status'  => $request->status
            ];
            $karyawan->notify(new \App\Notifications\IjinKunjunganNotification($details));
        }

        return response()->json([
            'success' => true,
            'message' => 'Status pengajuan ijin berhasil diupdate'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Gagal update status: ' . $e->getMessage()
        ], 500);
    }
}
}