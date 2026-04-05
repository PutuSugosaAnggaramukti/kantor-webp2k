<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use App\Models\DataKunjunganAdm;
use App\Models\Karyawan;
use App\Models\Nasabah; 
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\PelaporanExport;
use App\Exports\PelaporanDetailExport;

class PelaporanController extends Controller
{
   public function index(Request $request) 
    {
        // 1. Data Daftar AO yang sudah berkunjung (Data Lama) - Tetap
        $pelaporan_all = Karyawan::whereHas('kunjungan')
            ->with(['kunjungan' => function($query) {
                $query->orderBy('tanggal', 'desc');
            }])
            ->get();

        $pelaporan_all = $pelaporan_all->map(function ($karyawan) {
            $karyawan->kunjungan_terbaru = $karyawan->kunjungan->first();
            return $karyawan;
        });

        // 2. Data Daftar Nasabah yang SUDAH dikunjungi (Data Baru)
        // PERBAIKAN: Gunakan whereHas('kunjungan') alih-alih where('sudah_kunjung', 1)
        $nasabah_terkunjungi = Nasabah::whereHas('kunjungan') 
            ->with(['kunjungan' => function($query) {
                $query->orderBy('tanggal', 'desc');
            }])
            ->orderBy('nasabah', 'asc')
            ->get();

        if ($request->ajax()) {
            return view('admin.partials.pelaporan', compact('pelaporan_all', 'nasabah_terkunjungi'))->render();
        }

        $dashboard = new \App\Http\Controllers\Dashboard\DashboardAdminController();
        $data = $dashboard->getDashboardData();

        // Kirim kedua variabel ke view
        $data['content'] = view('admin.partials.pelaporan', compact('pelaporan_all', 'nasabah_terkunjungi'))->render();
        $data['page'] = 'pelaporan';
        $data['title'] = 'Pelaporan Kunjungan';

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

    public function exportDetailAo($id)
    {
        // 1. Cari data karyawan berdasarkan ID (untuk penamaan file)
        $ao = Karyawan::findOrFail($id);

        // 2. Buat nama file yang rapi (Contoh: Laporan_Wahyu_Nugroho_04-04-2026.xlsx)
        $timestamp = now()->format('d-m-Y');
        $namaAo = str_replace(' ', '_', $ao->nama);
        $fileName = "Laporan_Kunjungan_{$namaAo}_{$timestamp}.xlsx";

        // 3. Eksekusi download menggunakan class Export
        // Kita lempar $id ke constructor PelaporanDetailExport
        return Excel::download(new PelaporanDetailExport($id), $fileName);
    }

}
