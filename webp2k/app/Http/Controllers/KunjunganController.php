<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\DataKunjunganAdm;
use App\Models\Nasabah;
use App\Models\Karyawan;
use App\Models\Kunjungan;
use Illuminate\Http\Request;

class KunjunganController extends Controller
{
   public function index()
    {
        $karyawanId = Auth::guard('karyawan')->id();
        $data = DataKunjunganAdm::where('karyawan_id', $karyawanId)->get();

        return view('kunjungan.datakunjungan', compact('data'));
    }

    public function dataKunjunganContent()
    {
        $data = Nasabah::all();
        return view('kunjungan.partials.data_table', compact('data'));
    }

    public function laporanKunjunganContent()
    {
        $data = Nasabah::all();
        return view('kunjungan.partials.laporan_table', compact('data'));
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
