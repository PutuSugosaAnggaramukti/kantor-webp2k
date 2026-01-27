<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use App\Models\DataKunjunganAdm;
use App\Models\Karyawan;
use Illuminate\Http\Request;

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

}
