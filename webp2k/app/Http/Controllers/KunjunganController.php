<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use App\Models\Kunjungan;
use Illuminate\Http\Request;

class KunjunganController extends Controller
{
    public function index()
    {
        $data = Nasabah::all();
        return view('kunjungan.datakunjungan', compact('data'));
    }

    public function dataKunjunganContent()
    {
        $data = Nasabah::all();
        // Panggil PARTIAL tabelnya saja, bukan file utama
        return view('kunjungan.partials.data_table', compact('data'));
    }

    public function laporanKunjunganContent()
    {
        $data = Nasabah::all();
        // Ubah 'kunjungan.partials.laporan' menjadi 'kunjungan.partials.laporan_table'
        return view('kunjungan.partials.laporan_table', compact('data'));
    }

   public function showBukti($id)
    {
        $dataKunjungan = Kunjungan::findOrFail($id);

        // Jika diklik melalui tombol (AJAX)
        if (request()->ajax()) {
            return view('kunjungan.partials.bukti_kunjungan', compact('dataKunjungan'));
        }

        // Jika halaman di-refresh manual oleh user (Non-AJAX)
        return view('kunjungan.datakunjungan', [
            'data' => Kunjungan::all(), // Agar tabel di belakang tidak error
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
