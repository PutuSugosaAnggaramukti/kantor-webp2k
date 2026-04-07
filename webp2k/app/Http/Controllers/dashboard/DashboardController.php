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

        // --- LOGIKA BARU: HITUNG NOTIFIKASI UPDATE STATUS ---
        // Menghitung notifikasi database yang belum dibaca (unread) 
        // khusus untuk update status dari admin
        $notifCount = $user->unreadNotifications
            ->where('type', 'App\Notifications\UpdateStatusNotification')
            ->count();

        $rawCode = $user->kode_ao; 
        $cleanCode = preg_replace('/[^A-Za-z0-9]/', '', $rawCode); 

        $applyAoFilter = function($query) use ($rawCode, $cleanCode) {
            $query->where(function($q) use ($rawCode, $cleanCode) {
                $q->where('kode_ao', $rawCode)
                ->orWhereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanCode]);
            });
        };

        // --- DATA STATISTIK DASAR ---
        $total_rencana = DB::table('data_kunjungan_adms')->where($applyAoFilter)->count();
        $total_kunjungan = DB::table('kunjungans')->where($applyAoFilter)->count();
        $kunjungan_hari_ini = DB::table('kunjungans')
            ->whereDate('created_at', Carbon::today())
            ->where($applyAoFilter)->count();
        $belum_dikunjungi = max(0, $total_rencana - $total_kunjungan);

        // --- LOGIKA KOL 5 (Tetap sama) ---
        $rencana_admin = DB::table('data_kunjungan_adms')
            ->where('kol', 5)->where($applyAoFilter)
            ->select('nama_nasabah', 'alamat_nasabah as alamat', 'kol')->get();
        $nama_rencana = $rencana_admin->pluck('nama_nasabah')->toArray();
        $mandiri_kol5 = DB::table('kunjungans')
            ->where('kol', 5)->where($applyAoFilter)
            ->whereNotIn('nama_nasabah', $nama_rencana)
            ->select('nama_nasabah', DB::raw("'-' as alamat"), 'kol')->get();
        $detail_kol5_all = $rencana_admin->concat($mandiri_kol5);
        $wajib_kol5 = $detail_kol5_all->count();
        $nama_sudah_visit = DB::table('kunjungans')->where($applyAoFilter)->pluck('nama_nasabah')->toArray();
        $detail_kol5 = $detail_kol5_all->map(function ($item) use ($nama_sudah_visit) {
            $item->status_label = in_array($item->nama_nasabah, $nama_sudah_visit) ? 'Sudah Dikunjungi' : 'Wajib Dikunjungi Segera';
            return $item;
        });
        $kol5_terkunjungi = $detail_kol5->where('status_label', 'Sudah Dikunjungi')->count();
        $kpi_kol5 = $wajib_kol5 > 0 ? ($kol5_terkunjungi / $wajib_kol5) * 100 : 0;

        // --- LOGIKA KPI (Tetap sama) ---
        $kpi_target_harian = $total_rencana > 0 ? ($kunjungan_hari_ini / $total_rencana) * 100 : 0;
        $ketemu = DB::table('kunjungans')->where('ada_di_lokasi', 'Ada')->where($applyAoFilter)->count();
        $kpi_success_rate = $total_kunjungan > 0 ? ($ketemu / $total_kunjungan) * 100 : 0;
        $janji_bayar = DB::table('kunjungans')->whereNotNull('tgl_janji_bayar')->where($applyAoFilter)->count();
        $kpi_janji_bayar = $total_kunjungan > 0 ? ($janji_bayar / $total_kunjungan) * 100 : 0;

        // --- DATA DETAIL ---
        $detail_rencana = DB::table('data_kunjungan_adms')->where($applyAoFilter)->select('*', DB::raw("'Belum' as status"))->get();
        $detail_belum_dikunjungi = $detail_rencana->filter(fn($rencana) => !in_array($rencana->nama_nasabah, $nama_sudah_visit));
        $detail_sudah_dikunjungi = DB::table('kunjungans')->where($applyAoFilter)->select('nama_nasabah', 'ada_di_lokasi', 'tgl_janji_bayar', 'catatan', 'created_at')->get();

        // --- DATA GRAFIK ---
        $dataGrafik = DB::table('kunjungans') 
            ->selectRaw("DATE(created_at) as tgl, SUM(CASE WHEN ada_di_lokasi = 'Ada' THEN 1 ELSE 0 END) as ada, SUM(CASE WHEN ada_di_lokasi = 'Tidak Ada' THEN 1 ELSE 0 END) as tidak_ada")
            ->where($applyAoFilter)->where('created_at', '>=', now()->subDays(7)) 
            ->groupBy('tgl')->orderBy('tgl', 'asc')->get();
        
        // --- DATA RIWAYAT IJIN ---
        $list_pengajuan = DB::table('ijin_kunjungans')->where('karyawan_id', $user->id)->orderBy('created_at', 'desc')->get();
        $list_gagal_kunjungan = $list_pengajuan->where('status', 'disetujui');
        $total_gagal_kunjungan = $list_gagal_kunjungan->count();
        $notif_acc = $list_pengajuan->where('status', 'disetujui')->where('updated_at', '>=', now()->subHours(24))->first();

        return view('dashboard.dashboard', array_merge(
            compact('total_rencana', 'total_kunjungan', 'belum_dikunjungi', 'wajib_kol5', 'kunjungan_hari_ini', 'detail_rencana', 'detail_sudah_dikunjungi', 'detail_kol5',
            'detail_belum_dikunjungi','nama_sudah_visit','notif_acc','list_pengajuan','total_gagal_kunjungan','list_gagal_kunjungan', 
            'notifCount'), // <--- KIRIM VARIABLE NOTIF KE VIEW
            [
                'labels' => $dataGrafik->pluck('tgl'),
                'nasabahAda' => $dataGrafik->pluck('ada')->map(fn($v) => (int)$v),
                'nasabahTidakAda' => $dataGrafik->pluck('tidak_ada')->map(fn($v) => (int)$v),
                'kpi' => [
                    'target'  => round($kpi_target_harian),
                    'success' => round($kpi_success_rate),
                    'kol5'    => round($kpi_kol5),
                    'janji'   => round($kpi_janji_bayar)
                ]
            ]
        ));
    }
}