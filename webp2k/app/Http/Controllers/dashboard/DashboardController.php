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

        // 1. TOTAL RENCANA (Semua data dari tabel admin)
        $total_rencana = DB::table('data_kunjungan_adms')
            ->whereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanCode])
            ->count();
        
        // Detail untuk Modal Total Rencana
        $detail_rencana = DB::table('data_kunjungan_adms')
            ->whereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanCode])
            ->get();

        // 2. SUDAH DIKUNJUNGI
        $total_kunjungan = DB::table('kunjungans')
            ->whereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanCode])
            ->count();

        // Detail untuk Modal Sudah Dikunjungi
        $detail_sudah_dikunjungi = DB::table('kunjungans')
            ->whereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanCode])
            ->get();

        // 3. WAJIB KUNJUNGI (KOL 5)
        $wajib_kol5 = DB::table('data_kunjungan_adms')
            ->where('kol', 5)
            ->whereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanCode])
            ->count();

        // Detail untuk Modal KOL 5
        $detail_kol5 = DB::table('data_kunjungan_adms')
            ->where('kol', 5)
            ->whereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanCode])
            ->get();

        // 4. KUNJUNGI HARI INI
        $kunjungan_hari_ini = DB::table('kunjungans')
            ->whereDate('created_at', Carbon::today())
            ->whereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanCode])
            ->count();

        // --- DATA GRAFIK ---
        $dataGrafik = DB::table('kunjungans') 
            ->selectRaw("DATE(created_at) as tgl, 
                SUM(CASE WHEN ada_di_lokasi = 'Ada' THEN 1 ELSE 0 END) as ada, 
                SUM(CASE WHEN ada_di_lokasi = 'Tidak Ada' THEN 1 ELSE 0 END) as tidak_ada")
            ->whereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanCode])
            ->where('created_at', '>=', now()->subDays(7)) 
            ->groupBy('tgl')
            ->orderBy('tgl', 'asc')
            ->get();

        $labels = $dataGrafik->pluck('tgl');
        $nasabahAda = $dataGrafik->pluck('ada')->map(fn($val) => (int)$val);
        $nasabahTidakAda = $dataGrafik->pluck('tidak_ada')->map(fn($val) => (int)$val);

        return view('dashboard.dashboard', compact(
            'labels', 'nasabahAda', 'nasabahTidakAda', 
            'total_rencana', 'total_kunjungan', 'wajib_kol5', 'kunjungan_hari_ini',
            'detail_rencana', 'detail_sudah_dikunjungi', 'detail_kol5'
        ));
    }
}