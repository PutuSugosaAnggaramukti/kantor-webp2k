<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use App\Models\DataKunjunganAdm;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class AdmKunjunganController extends Controller
{
   public function index()
    {
        $karyawans = Karyawan::all();
        $kunjungans = DataKunjunganAdm::with('karyawan')->latest()->get();


        return view('admin.partials.input_kunjungan', compact('karyawans', 'kunjungans'));
    }

    public function dataKunjunganContent()
    {
        $karyawan = Karyawan::withCount('kunjungan')->get();

        return view('admin.partials.kunjungan', compact('karyawan'));
    }

   public function detail($kode_ao)
    {
        try {
            $data_detail = DataKunjunganAdm::whereHas('karyawan', function($q) use ($kode_ao) {
                    $q->where('kode_ao', $kode_ao);
                })
                // Tambahkan orderBy agar rapi
                ->orderBy('tanggal', 'desc')
                ->get();

            // Pastikan path view sesuai dengan lokasi file kamu
            return view('admin.partials.detail_kunjungan', compact('data_detail', 'kode_ao'));
            
        } catch (\Exception $e) {
            return "<div style='color:red; padding:20px;'>Error: " . $e->getMessage() . "</div>";
        }
    }

    public function store(Request $request)
    {
        // Cek dulu apakah data masuk ke server atau tidak dengan ini (opsional untuk debug)
        // dd($request->all()); 

        \App\Models\DataKunjunganAdm::create([
            'karyawan_id'  => $request->karyawan_id,
            'nama_nasabah' => $request->nama_nasabah,
            'alamat_nasabah' => $request->alamat_nasabah,
            'kol'          => $request->kol,
            'bulan'        => $request->bulan,
            'no_angsuran'  => $request->no_angsuran,
            'tanggal'      => $request->tanggal, 
            'kode_ao'      => Karyawan::find($request->karyawan_id)->kode_ao ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Data berhasil disimpan']);
    }

    public function rekapKunjungan()
    {
        $rekap = Karyawan::withCount(['kunjungan as jumlah_kunjungan'])
            ->get();

        return view('admin.rekap_kunjungan_content', compact('rekap'));
    }
}
