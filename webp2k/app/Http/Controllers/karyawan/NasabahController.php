<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use App\Models\DataKunjunganAdm;
use Illuminate\Http\Request;

class NasabahController extends Controller
{

    public function nasabahContent()
    {
        $nasabah_all = DataKunjunganAdm::select(
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

}
