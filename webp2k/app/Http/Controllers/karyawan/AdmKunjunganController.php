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

    public function store(Request $request)
    {
        $request->validate([
            'karyawan_id'  => 'required',
            'nama_nasabah' => 'required',
            'kol'          => 'required',
            'bulan'        => 'required',
        ]);
        $karyawan = Karyawan::findOrFail($request->karyawan_id);

        DataKunjunganAdm::create([
            'kode_ao'      => $karyawan->kode_ao,
            'nama_nasabah' => $request->nama_nasabah,
            'kol'          => $request->kol,
            'bulan'        => $request->bulan,
            'karyawan_id'  => $karyawan->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Jadwal kunjungan berhasil dibuat!']);
    }

    public function rekapKunjungan()
    {
        $rekap = Karyawan::withCount(['kunjungan as jumlah_kunjungan'])
            ->get();

        return view('admin.rekap_kunjungan_content', compact('rekap'));
    }
}
