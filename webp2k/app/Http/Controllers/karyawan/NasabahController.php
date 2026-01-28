<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use App\Exports\NasabahExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\DataKunjunganAdm;
use Illuminate\Http\Request;

class NasabahController extends Controller
{

  public function nasabahContent(Request $request)
    {
        $query = DataKunjunganAdm::query();

        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal', [$request->tanggal_awal, $request->tanggal_akhir]);
        }

        $nasabah_all = $query->select(
                'no_angsuran', 
                'nama_nasabah', 
                \DB::raw('count(karyawan_id) as jml_pengunjung')
            )
            ->groupBy('no_angsuran', 'nama_nasabah')
            ->orderBy('nama_nasabah', 'asc')
            ->get();

        return view('admin.partials.nasabah_table', compact('nasabah_all'));
    }

    public function detail($no_angsuran)
    {
        $histori_kunjungan = DataKunjunganAdm::where('no_angsuran', $no_angsuran)
            ->with('karyawan')
            ->get();

        // Pastikan view-nya mengarah ke file yang sedang kita edit
        return view('admin.partials.pengunjung_nasabah', compact('histori_kunjungan'));
    }

    public function exportExcel(Request $request)
    {
        $tglAwal = $request->tanggal_awal;
        $tglAkhir = $request->tanggal_akhir;

        // Nama file rapi berdasarkan rentang waktu
        $namaFile = 'Rekap_Nasabah_' . $tglAwal . '_sd_' . $tglAkhir . '.xlsx';

        return Excel::download(new NasabahExport($tglAwal, $tglAkhir), $namaFile);
    }

}
