<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Kunjungan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
      public function index()
    {
        $user = auth()->user();

        // 1. Ambil data untuk Grafik Batang
        $dataGrafik = Kunjungan::where('kode_ao', $user->kode_ao)
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw("DATE(created_at) as tgl, 
                SUM(CASE WHEN ada_di_lokasi = 'Ada' THEN 1 ELSE 0 END) as ada,
                SUM(CASE WHEN ada_di_lokasi = 'Tidak Ada' THEN 1 ELSE 0 END) as tidak_ada")
            ->groupBy('tgl')
            ->orderBy('tgl', 'asc')
            ->get();

        $labels = $dataGrafik->pluck('tgl');
        $nasabahAda = $dataGrafik->pluck('ada')->map(fn($val) => (int)$val);
        $nasabahTidakAda = $dataGrafik->pluck('tidak_ada')->map(fn($val) => (int)$val);

        // 2. Hitung statistik untuk kotak di dashboard (WAJIB DITAMBAHKAN)
        $total_kunjungan = Kunjungan::where('kode_ao', $user->kode_ao)->count();
        $sudah_dikunjungi = Kunjungan::where('kode_ao', $user->kode_ao)
                                    ->where('ada_di_lokasi', 'Ada')
                                    ->count();

        // 3. Kirim semua variabel ke view dashboard/dashboard.blade.php
        return view('dashboard.dashboard', compact('labels', 'nasabahAda', 'nasabahTidakAda', 'total_kunjungan','sudah_dikunjungi'
        ));
    }
}
