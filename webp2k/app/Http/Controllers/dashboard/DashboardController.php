<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; 

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('karyawan')->user();
        if (!$user) return redirect()->route('login');
        $rawCode = $user->kode_ao; 
        $cleanCode = preg_replace('/[^A-Za-z0-9]/', '', $rawCode); 

        // --- DATA STATISTIK DASAR ---
        $total_rencana = DB::table('data_kunjungan_adms')
            ->whereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanCode])->count();
        
        $total_kunjungan = DB::table('kunjungans')
            ->whereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanCode])->count();

        $wajib_kol5 = DB::table('data_kunjungan_adms')
            ->where('kol', 5)
            ->whereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanCode])->count();

        $kunjungan_hari_ini = DB::table('kunjungans')
            ->whereDate('created_at', Carbon::today())
            ->whereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanCode])->count();

        // --- LOGIKA KPI (PERSENTASE) ---

        // 1. Target Harian (Min 10)
        $kpi_target_harian = min(($kunjungan_hari_ini / 10) * 100, 100);

        // 2. Success Rate (Ketemu Nasabah)
        $ketemu = DB::table('kunjungans')
            ->where('ada_di_lokasi', 'Ada')
            ->whereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanCode])->count();
        $kpi_success_rate = $total_kunjungan > 0 ? ($ketemu / $total_kunjungan) * 100 : 0;

        // 3. Penanganan KOL 5
        $kol5_terkunjungi = DB::table('kunjungans')
            ->where('kol', 5)
            ->whereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanCode])->count();
        $kpi_kol5 = $wajib_kol5 > 0 ? ($kol5_terkunjungi / $wajib_kol5) * 100 : 0;

        // 4. Janji Bayar Rate
        $janji_bayar = DB::table('kunjungans')
            ->whereNotNull('tgl_janji_bayar')
            ->whereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanCode])->count();
        $kpi_janji_bayar = $total_kunjungan > 0 ? ($janji_bayar / $total_kunjungan) * 100 : 0;

        // Data Detail untuk Modal (Tetap sama)
        $detail_rencana = DB::table('data_kunjungan_adms')->whereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanCode])->get();
        $detail_sudah_dikunjungi = DB::table('kunjungans')->whereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanCode])->get();
        $detail_kol5 = DB::table('data_kunjungan_adms')->where('kol', 5)->whereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanCode])->get();

        // Data Grafik Batang (Tetap dipertahankan sebagai histori 7 hari)
        $dataGrafik = DB::table('kunjungans') 
            ->selectRaw("DATE(created_at) as tgl, 
                SUM(CASE WHEN ada_di_lokasi = 'Ada' THEN 1 ELSE 0 END) as ada, 
                SUM(CASE WHEN ada_di_lokasi = 'Tidak Ada' THEN 1 ELSE 0 END) as tidak_ada")
            ->whereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanCode])
            ->where('created_at', '>=', now()->subDays(7)) 
            ->groupBy('tgl')->orderBy('tgl', 'asc')->get();

        return view('dashboard.dashboard', array_merge(
            compact('total_rencana', 'total_kunjungan', 'wajib_kol5', 'kunjungan_hari_ini', 'detail_rencana', 'detail_sudah_dikunjungi', 'detail_kol5'),
            [
                'labels' => $dataGrafik->pluck('tgl'),
                'nasabahAda' => $dataGrafik->pluck('ada')->map(fn($v) => (int)$v),
                'nasabahTidakAda' => $dataGrafik->pluck('tidak_ada')->map(fn($v) => (int)$v),
                'kpi' => [
                    'target' => round($kpi_target_harian),
                    'success' => round($kpi_success_rate),
                    'kol5' => round($kpi_kol5),
                    'janji' => round($kpi_janji_bayar)
                ]
            ]
        ));
    }
}