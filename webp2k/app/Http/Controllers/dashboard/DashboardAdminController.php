<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\DataKunjunganAdm;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardAdminController extends Controller
{
    public function index()
    {
        $totalKunjungan = \App\Models\DataKunjunganAdm::count();

        $performaAO = \App\Models\Karyawan::where('status', 'aktif')->get()->map(function ($karyawan) {
            $kodeAO = trim($karyawan->kode_ao);

            // 1. Ambil semua kunjungan AO ini
            $kunjunganUser = \DB::table('kunjungans')
                ->where('kode_ao', $kodeAO)
                ->get();

            $karyawan->kunjungan_selesai = $kunjunganUser->count();
            
            // 2. Ambil daftar nama nasabah yang dikunjungi (karena no_nasabah kamu isinya salah)
            $daftarNamaNasabah = $kunjunganUser->pluck('nama_nasabah')->toArray();

            // 3. Cek ke tabel nasabahs berdasarkan NAMA nasabah
            $hasKol5 = \DB::table('nasabahs')
                ->whereIn('nasabah', $daftarNamaNasabah) // 'nasabah' adalah kolom nama di tabel nasabahs
                ->where('kol', '5')
                ->exists();

            $karyawan->capai_target = ($karyawan->kunjungan_selesai >= 10 && $hasKol5);

            return $karyawan;
        });

        $totalSelesai = $performaAO->sum('kunjungan_selesai');
        $totalBelum = max(0, $totalKunjungan - $totalSelesai);
        $aoSelesaiTarget = $performaAO->where('capai_target', true)->count();

        // Data Grafik
        $labels = $performaAO->pluck('nama'); 
        $counts = $performaAO->pluck('kunjungan_selesai'); 

        return view('dashboard.dashboardadmin', compact(
            'totalKunjungan', 'totalSelesai', 'totalBelum', 'aoSelesaiTarget', 'labels', 'counts'
        ));
    }
}