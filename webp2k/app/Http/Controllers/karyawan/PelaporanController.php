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
    public function index(Request $request) 
    {
        $pelaporan_all = Karyawan::whereHas('kunjungan')
            ->with(['kunjungan' => function($query) {
                $query->orderBy('tanggal', 'desc');
            }])
            ->get();

        $pelaporan_all = $pelaporan_all->map(function ($karyawan) {
            $karyawan->kunjungan_terbaru = $karyawan->kunjungan->first();
            return $karyawan;
        });

        if ($request->ajax()) {
            return view('admin.partials.pelaporan', compact('pelaporan_all'))->render();
        }
        $dashboard = new \App\Http\Controllers\Dashboard\DashboardAdminController();
        $data = $dashboard->getDashboardData();

        $data['content'] = view('admin.partials.pelaporan', compact('pelaporan_all'))->render();
        $data['page'] = 'pelaporan';
        $data['title'] = 'Pelaporan';

        
        return view('admin.datakaryawan', $data);
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

        $fileName = 'Laporan_Kunjungan_' . $tgl_awal . '_to_' . $tgl_akhir . '.xlsx';

        return Excel::download(new PelaporanExport($tgl_awal, $tgl_akhir), $fileName);
    }

}
