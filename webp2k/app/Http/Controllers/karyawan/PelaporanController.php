<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use App\Models\DataKunjunganAdm;
use App\Models\Karyawan;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\PelaporanExport;

class PelaporanController extends Controller
{
   public function index()
    {
        $pelaporan_all = DataKunjunganAdm::with('karyawan')
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('admin.partials.pelaporan', compact('pelaporan_all'));
    }

    public function detailAo($id_ao)
    {
        $histori_ao = DataKunjunganAdm::where('karyawan_id', $id_ao)
            ->orWhere('kode_ao', $id_ao)
            ->orderBy('tanggal', 'desc')
            ->get();

        $ao = Karyawan::where('id', $id_ao)
            ->orWhere('kode_ao', $id_ao)
            ->first();

        return view('admin.partials.pelaporan_detail', compact('histori_ao', 'ao'));
    }

    public function exportExcel(Request $request)
    {
        $tgl_awal = $request->tanggal_awal;
        $tgl_akhir = $request->tanggal_akhir;

        // Nama file yang akan diunduh
        $fileName = 'Laporan_Kunjungan_' . $tgl_awal . '_to_' . $tgl_akhir . '.xlsx';

        // Menjalankan proses download Excel
        return Excel::download(new PelaporanExport($tgl_awal, $tgl_akhir), $fileName);
    }

}
