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
        // Ambil keyword dari input search
        $keyword = $request->search;

        // 1. Data Daftar AO (Tabel Atas)
        // Filter dihapus agar tabel ini tidak ikut berubah saat mengetik di search
        $pelaporan_all = Karyawan::whereHas('kunjungan')
            ->with(['kunjungan' => function($query) {
                $query->orderBy('tanggal', 'desc');
            }])
            ->get();

        $pelaporan_all = $pelaporan_all->map(function ($karyawan) {
            $karyawan->kunjungan_terbaru = $karyawan->kunjungan->first();
            return $karyawan;
        });

        // 2. Data Daftar Nasabah (Tabel Bawah)
        // Tetap menggunakan filter agar data menyusut sesuai pencarian
        $nasabah_terkunjungi = Nasabah::whereHas('kunjungan') 
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('nasabah', 'like', "%{$keyword}%")
                    ->orWhere('no_angsuran', 'like', "%{$keyword}%");
                });
            })
            ->with(['kunjungan.karyawan']) // Eager load karyawan agar tidak N+1
            ->orderBy('nasabah', 'asc')
            ->get();

        // Jika Request AJAX (saat user mengetik di search)
        if ($request->ajax()) {
            return view('admin.partials.pelaporan', compact('pelaporan_all', 'nasabah_terkunjungi'))->render();
        }

        // Load data dashboard seperti biasa untuk tampilan awal
        $dashboard = new \App\Http\Controllers\Dashboard\DashboardAdminController();
        $data = $dashboard->getDashboardData();

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
