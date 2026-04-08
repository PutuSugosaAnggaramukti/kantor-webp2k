<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\DataKunjunganAdm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardAdminController extends Controller
{
   public function index()
    {

        $data = $this->getDashboardData();
        return view('dashboard.dashboardadmin', $data);
        
    }
    
   public function getDashboardData()
    {
        
       // --- 1. DATA DASAR ---
        $totalKunjungan = \App\Models\DataKunjunganAdm::count(); 

        // --- UPDATED: LOGIKA GAGAL KUNJUNGAN (IJIN DISETUJUI) ---

        // Ambil semua ijin yang statusnya 'disetujui' (Gagal Kunjungan)
        // Kita join dengan karyawan untuk mendapatkan Nama AO-nya
        $list_gagal_kunjungan_all = \App\Models\IjinKunjungan::with('karyawan')
            ->where('status', 'disetujui')
            ->orderBy('tanggal', 'desc')
            ->get();

        // Total untuk angka di Card Merah (Gagal Kunjungan)
        $total_gagal_global = $list_gagal_kunjungan_all->count();
        
        // Ambil user login
        $user = Auth::user();

        // List pengajuan (tetap ada untuk modal konfirmasi ijin jika diperlukan)
       // 1. Ambil list untuk isi modal (tetap ambil 50 data terakhir)
        $list_pengajuan = \App\Models\IjinKunjungan::with('karyawan')
            ->orderBy('created_at', 'desc')
            ->take(50) 
            ->get();

        // 3. Logika Pembersihan: Jika sudah tidak ada ijin yang 'pending', 
        // maka otomatis tandai semua notif sebagai 'sudah dibaca' agar angka merah hilang.
        $ijinPendingExists = \App\Models\IjinKunjungan::where('status', 'pending')->exists();

        if (!$ijinPendingExists && $user) {
            $user->unreadNotifications
                ->where('type', 'App\Notifications\IjinKunjunganNotification')
                ->markAsRead();
        }
        
        // 4. Hitung ulang sisa notifikasi yang belum dibaca untuk ditampilkan di badge
        $pengajuan_ijin_count = $user ? $user->unreadNotifications
            ->where('type', 'App\Notifications\IjinKunjunganNotification')
            ->count() : 0;

        $performaAO = \App\Models\Karyawan::where('status', 'aktif')->get()->map(function ($karyawan) {
            $kodeAO = trim($karyawan->kode_ao);
            
            $kunjunganUser = \DB::table('kunjungans')
                ->where('kode_ao', $kodeAO)
                ->get();

            $karyawan->kunjungan_selesai = $kunjunganUser->count();
            $daftarNamaNasabahSelesai = $kunjunganUser->pluck('nama_nasabah')->toArray();

            $rencanaAO = \DB::table('data_kunjungan_adms')->where('kode_ao', $kodeAO)->count();
            $karyawan->persen_target = $rencanaAO > 0 
                ? round(($karyawan->kunjungan_selesai / $rencanaAO) * 100) 
                : 0;

            $rencanaKOL5AO = \DB::table('data_kunjungan_adms')
                ->where('kode_ao', $kodeAO)
                ->where('kol', 5)
                ->pluck('nama_nasabah')->toArray();

            $mandiriKOL5AO = \DB::table('kunjungans')
                ->where('kode_ao', $kodeAO)
                ->where('kol', 5)
                ->whereNotIn('nama_nasabah', $rencanaKOL5AO)
                ->pluck('nama_nasabah')->toArray();

            $gabunganWajibKOL5 = array_unique(array_merge($rencanaKOL5AO, $mandiriKOL5AO));
            $totalWajibKOL5AO = count($gabunganWajibKOL5);
            
            $selesaiKOL5AO = 0;
            foreach ($gabunganWajibKOL5 as $nama) {
                if (in_array($nama, $daftarNamaNasabahSelesai)) {
                    $selesaiKOL5AO++;
                }
            }

            $karyawan->persen_kol5 = $totalWajibKOL5AO > 0 
                ? round(($selesaiKOL5AO / $totalWajibKOL5AO) * 100) 
                : 0;

            $hasKol5 = \DB::table('nasabahs')
                ->whereIn('nasabah', $daftarNamaNasabahSelesai) 
                ->where('kol', '5')
                ->exists();

            $karyawan->capai_target = ($karyawan->kunjungan_selesai >= 10 && $hasKol5);
            
            return $karyawan;
        });

        // --- 2. LOGIKA AGREGAT NASIONAL ---
        $totalSelesai = $performaAO->sum('kunjungan_selesai');
        $totalBelum = max(0, $totalKunjungan - $totalSelesai);
        $aoSelesaiTarget = $performaAO->where('capai_target', true)->count();

        $kpi_target_nasional = $totalKunjungan > 0 ? round(($totalSelesai / $totalKunjungan) * 100) : 0;

        $target_kol5_nama = \DB::table('data_kunjungan_adms')->where('kol', 5)->pluck('nama_nasabah')->toArray();
        $mandiri_kol5_nama = \DB::table('kunjungans')->where('kol', 5)->whereNotIn('nama_nasabah', $target_kol5_nama)->pluck('nama_nasabah')->toArray();
        $gabungan_kol5_nasional = array_unique(array_merge($target_kol5_nama, $mandiri_kol5_nama));
        $total_wajib_kol5 = count($gabungan_kol5_nasional);

        $nama_sudah_visit = \DB::table('kunjungans')->pluck('nama_nasabah')->toArray();
        $kol5_done_count = 0;
        foreach ($gabungan_kol5_nasional as $nama) {
            if (in_array($nama, $nama_sudah_visit)) $kol5_done_count++;
        }

        $kpi_kol5_nasional = $total_wajib_kol5 > 0 ? round(($kol5_done_count / $total_wajib_kol5) * 100) : 0;

        $labels = $performaAO->pluck('nama'); 
        $counts = $performaAO->pluck('kunjungan_selesai'); 

        // --- 3. LOGIKA UNTUK ACCORDION NASABAH ---
        $kunjungsGrouped = \App\Models\Nasabah::with('karyawan')
            ->orderByRaw("kol = 5 DESC")
            ->get()
            ->groupBy('kode_ao');

        // Return array untuk compact-an
        return [
            'totalKunjungan' => $totalKunjungan,
            'totalSelesai' => $totalSelesai,
            'totalBelum' => $totalBelum,
            'aoSelesaiTarget' => $aoSelesaiTarget,
            'labels' => $labels,
            'counts' => $counts,
            'kpi_target_nasional' => $kpi_target_nasional,
            'kpi_kol5_nasional' => $kpi_kol5_nasional,
            'total_wajib_kol5' => $total_wajib_kol5,
            'detailPerformaAO' => $performaAO,
            'kunjungansGrouped' => $kunjungsGrouped,
            // Tambahkan dua variabel baru ini:
            'pengajuan_ijin_count' => $pengajuan_ijin_count,
            'list_pengajuan' => $list_pengajuan
        ];
    }

   public function getDetail($type)
    {
        try {
            $data = [];

            if ($type == 'rencana') {
                $data = \DB::table('data_kunjungan_adms')
                    ->join('karyawans', 'data_kunjungan_adms.kode_ao', '=', 'karyawans.kode_ao')
                    ->select(
                        'data_kunjungan_adms.kode_ao',
                        'karyawans.nama as nama_ao',
                        'data_kunjungan_adms.nama_nasabah',
                        'data_kunjungan_adms.tanggal'
                    )
                    ->get()->map(function($item) {
                        return [
                            'info_1' => $item->kode_ao . ' - ' . $item->nama_ao,
                            'info_2' => $item->nama_nasabah,
                            'info_3' => date('d-m-Y', strtotime($item->tanggal)),
                            'status' => 'Rencana'
                        ];
                    });

            } elseif ($type == 'selesai') {
                $data = \DB::table('kunjungans')
                    ->join('karyawans', 'kunjungans.kode_ao', '=', 'karyawans.kode_ao')
                    ->select(
                        'kunjungans.kode_ao', 
                        'karyawans.nama as nama_ao', 
                        'kunjungans.nama_nasabah', 
                        'kunjungans.created_at'
                    )
                    ->get()->map(function($item) {
                        return [
                            'info_1' => $item->kode_ao . ' - ' . $item->nama_ao,
                            'info_2' => $item->nama_nasabah,
                            'info_3' => date('d-m-Y', strtotime($item->created_at)),
                            'status' => 'Sudah Dikunjungi'
                        ];
                    });

            } elseif ($type == 'belum') {
                $sudahKunjung = \DB::table('kunjungans')->pluck('nama_nasabah')->toArray();
                
                $data = \DB::table('data_kunjungan_adms')
                    ->join('karyawans', 'data_kunjungan_adms.kode_ao', '=', 'karyawans.kode_ao')
                    ->whereNotIn('data_kunjungan_adms.nama_nasabah', $sudahKunjung)
                    ->select(
                        'data_kunjungan_adms.kode_ao',
                        'karyawans.nama as nama_ao',
                        'data_kunjungan_adms.nama_nasabah',
                        'data_kunjungan_adms.tanggal'
                    )
                    ->get()->map(function($item) {
                        return [
                            'info_1' => $item->kode_ao . ' - ' . $item->nama_ao,
                            'info_2' => $item->nama_nasabah,
                            'info_3' => date('d-m-Y', strtotime($item->tanggal)),
                            'status' => 'Belum Dikunjungi'
                        ];
                    });

            } elseif ($type == 'target') {
                $dashboard = new \App\Http\Controllers\dashboard\DashboardAdminController();
                $dashboardData = $dashboard->getDashboardData();
                
                $kodeAOTarget = $dashboardData['detailPerformaAO']
                    ->where('capai_target', true)
                    ->pluck('kode_ao')
                    ->toArray();

                $data = \DB::table('kunjungans')
                    ->join('karyawans', 'kunjungans.kode_ao', '=', 'karyawans.kode_ao')
                    ->whereIn('kunjungans.kode_ao', $kodeAOTarget)
                    ->select(
                        'kunjungans.kode_ao',
                        'karyawans.nama as nama_ao',
                        'kunjungans.nama_nasabah',
                        'kunjungans.created_at'
                    )
                    ->get()
                    ->filter(function($kunjungan) {
                        $namaClean = strtoupper(trim($kunjungan->nama_nasabah));
                        $cekNasabah = \DB::table('nasabahs')
                            ->whereRaw("UPPER(TRIM(nasabah)) = ?", [$namaClean])
                            ->where('kol', 5)
                            ->first();
                        return !is_null($cekNasabah);
                    })
                    ->values()
                    ->map(function($item) {
                        return [
                            'info_1' => $item->kode_ao . ' - ' . $item->nama_ao,
                            'info_2' => $item->nama_nasabah, 
                            'info_3' => date('d-m-Y', strtotime($item->created_at)),
                            'status' => 'KOL 5 Selesai'
                        ];
                    });

            // --- TAMBAHKAN LOGIKA BARU DI SINI ---
            } elseif ($type == 'gagal') {
                $data = \App\Models\IjinKunjungan::with('karyawan')
                    ->where('status', 'disetujui')
                    ->get()->map(function($item) {
                        return [
                            'info_1' => ($item->karyawan->kode_ao ?? $item->kode_ao) . ' - ' . ($item->karyawan->nama ?? 'N/A'),
                            'info_2' => $item->alasan, // Ini akan masuk ke kolom "Nama Nasabah" di modal
                            'info_3' => date('d-m-Y', strtotime($item->tanggal)),
                            'status' => 'Gagal Kunjungan'
                        ];
                    });
            }

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function markAsRead()
    {
        try {
            $user = Auth::user();

            if ($user) {
                // Mencari notifikasi yang belum dibaca khusus untuk Ijin Kunjungan
                $user->unreadNotifications
                    ->where('type', 'App\Notifications\IjinKunjunganNotification')
                    ->markAsRead();
            }

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil ditandai sudah dibaca'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'error' => $e->getMessage()
            ], 500);
        }
    }
}