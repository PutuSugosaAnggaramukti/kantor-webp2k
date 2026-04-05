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

            // 2. Cek User & Kode AO (Penting!)
            $user = auth()->user();
            
            if (!$user) {
                return response()->json(['error' => 'Sesi login habis, silakan login ulang.'], 401);
            }

            if (empty($user->kode_ao)) {
                return response()->json(['error' => 'User login tidak memiliki Kode AO. Cek tabel users!'], 422);
            }

            // 3. Simpan
            IjinKunjungan::create([
                'karyawan_id' => $user->id,
                'kode_ao' => $user->kode_ao,
                'tanggal' => $request->tanggal,
                'jenis_ijin' => $request->jenis_ijin,
                'alasan' => $request->alasan,
            ]);

            return response()->json(['message' => 'Ijin berhasil dikirim']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Balikan error validasi (422) jika ada field yang kosong
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Balikan error sistem (500)
            return response()->json(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }

        // 1. Menampilkan semua pengajuan ijin ke dashboard Admin
    public function indexAdmin()
    {
        // Mengambil data ijin beserta relasi karyawannya
        $dataIjin = IjinKunjungan::with('karyawan')->orderBy('created_at', 'desc')->get();
        return view('admin.ijin.index', compact('dataIjin'));
    }

    // 2. Memproses perubahan status (Setuju/Tolak)
    public function updateStatus(Request $request, $id)
    {
        try {
            $ijin = IjinKunjungan::findOrFail($id);
            
            $ijin->update([
                'status' => $request->status // berisi 'disetujui' atau 'ditolak'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status pengajuan ijin berhasil diupdate ke: ' . $request->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal update status: ' . $e->getMessage()
            ], 500);
        }
    }
}