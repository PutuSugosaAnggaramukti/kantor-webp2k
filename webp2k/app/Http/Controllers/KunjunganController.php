<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\DataKunjunganAdm;
use App\Models\HasilKunjungan;
use App\Models\Nasabah;
use App\Models\Karyawan;
use App\Models\Kunjungan;
use Illuminate\Http\Request;

class KunjunganController extends Controller
{
    public function index()
    {
        $karyawanId = Auth::guard('karyawan')->id();
        
        if (!$karyawanId) {
            return "<div class='alert alert-warning'>Sesi berakhir, silakan refresh halaman.</div>";
        }
    
        $data = DataKunjunganAdm::where('karyawan_id', $karyawanId)->get();
        if (request()->ajax()) {
            return view('kunjungan.partials.data_table', compact('data'));
        }
        return view('kunjungan.datakunjungan', compact('data'));
    }

    public function dataKunjunganContent()
    {
        $karyawanId = Auth::guard('karyawan')->id();
        $data = DataKunjunganAdm::where('karyawan_id', $karyawanId)->get();
    
        return view('kunjungan.partials.data_table', compact('data'));
    }

    public function laporanKunjunganContent()
    {
        $data = Nasabah::all();
        return view('kunjungan.partials.laporan_table', compact('data'));
    }

    public function indexpelaporan()
    {
        try {
            // 1. Ambil ID Karyawan (AO) yang sedang login
            $karyawanId = Auth::guard('karyawan')->id();
    
            // 2. Filter data agar hanya mengambil milik AO yang login (karyawan_id)
            $laporan = DataKunjunganAdm::where('karyawan_id', $karyawanId)
                        ->with(['karyawan', 'hasilKunjungan'])
                        ->get();
    
            return view('kunjungan.partials.laporan_table', compact('laporan'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function detailPelaporan(Request $request)
    {
        $id = $request->query('id');
        $karyawanId = Auth::guard('karyawan')->id();

        // Cari detail laporan, tapi pastikan data jadwalnya (dataKunjungan) milik AO yang login
        $detail = HasilKunjungan::whereHas('dataKunjungan', function($query) use ($karyawanId) {
            $query->where('karyawan_id', $karyawanId);
        })->find($id);

        if (!$detail) {
            return "<div class='alert alert-danger'>Akses ditolak atau data tidak ditemukan.</div>";
        }

        return view('kunjungan.partials.bukti_kunjungan', compact('detail'));
    }

   public function showBukti($id)
    {
        $dataKunjungan = Kunjungan::findOrFail($id);

        if (request()->ajax()) {
            return view('kunjungan.partials.bukti_kunjungan', compact('dataKunjungan'));
        }

        return view('kunjungan.datakunjungan', [
            'data' => Kunjungan::all(), 
            'content' => view('kunjungan.partials.bukti_kunjungan', compact('dataKunjungan'))->render()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'foto_kunjungan' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Maks 5MB
        ]);

        // Proses simpan file foto
        if ($request->hasFile('foto_kunjungan')) {
            $file = $request->file('foto_kunjungan');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads/kunjungan', $filename, 'public');
        }

        // Simpan ke database
        Kunjungan::create([
            'no_nasabah' => $request->no_nasabah,
            'nama_nasabah' => $request->nama_nasabah,
            'keterangan_nasabah' => $request->keterangan_nasabah,
            'ada_di_lokasi' => $request->ada_di_lokasi,
            'catatan' => $request->catatan,
            'foto_kunjungan' => $path,
            'koordinat' => $request->koordinat ?? '-7.888, 110.323', // Contoh default
        ]);

        return redirect()->back()->with('success', 'Data kunjungan berhasil disimpan!');
    }
    

}
