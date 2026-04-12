<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\DataKunjunganAdm;
use App\Models\IjinKunjungan;
use App\Models\Kunjungan;
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

    // --- LOGIKA GAGAL KUNJUNGAN (IJIN DISETUJUI) ---
    $list_gagal_kunjungan_all = \App\Models\IjinKunjungan::with('karyawan')
        ->whereIn('status', ['disetujui', 'DISETUJUI']) 
        ->orderBy('tanggal', 'desc')
        ->get();

    $total_gagal_global = $list_gagal_kunjungan_all->count();
    $user = Auth::user();

    $list_pengajuan = \App\Models\IjinKunjungan::with('karyawan')
        ->orderBy('created_at', 'desc')
        ->take(50) 
        ->get();

    $ijinPendingExists = \App\Models\IjinKunjungan::where('status', 'pending')->exists();

    if (!$ijinPendingExists && $user) {
        $user->unreadNotifications
            ->where('type', 'App\Notifications\IjinKunjunganNotification')
            ->markAsRead();
    }
    
    $pengajuan_ijin_count = $user ? $user->unreadNotifications
        ->where('type', 'App\Notifications\IjinKunjunganNotification')
        ->count() : 0;

    // --- LOGIKA PERFORMA AO (INDIVIDU) ---
   $performaAO = \App\Models\Karyawan::where('status', 'aktif')->get()->map(function ($karyawan) {
        $kodeAO = trim($karyawan->kode_ao);
        
        // 1. Ambil SEMUA kunjungan fisik
        $semuaKunjungan = \DB::table('kunjungans')
            ->where('kode_ao', $kodeAO)
            ->get();

        // 2. LOGIKA ANTI-DUPLIKAT (Realisasi)
        // Di tabel 'kunjungans', no_nasabah biasanya tersedia.
        $kunjunganUnik = \DB::table('kunjungans')
            ->where('kode_ao', $kodeAO)
            ->distinct()
            ->pluck('no_nasabah') 
            ->toArray();

        $karyawan->kunjungan_selesai = count($kunjunganUnik);
        $karyawan->total_kunjungan_fisik = $semuaKunjungan->count();

        // 3. HITUNG TARGET (Jadwal Unik)
        // Ganti 'no_nasabah' dengan 'no_angsuran' atau 'nama_nasabah' agar tidak error
        $rencanaAO = \DB::table('data_kunjungan_adms')
            ->where('kode_ao', $kodeAO)
            ->distinct()
            ->count('no_angsuran'); // Gunakan kolom yang pasti ada di tabel jadwal Anda

        // 4. PERSENTASE TARGET
        if ($rencanaAO > 0) {
            $persenRaw = ($karyawan->kunjungan_selesai / $rencanaAO) * 100;
            
            // Mencegah angka 400% di UI: Batasi tampilan maksimal 100%
            // Jika Anda ingin tetap melihat '400%', hapus min(..., 100)
            $karyawan->persen_target = round(min($persenRaw, 100)); 
        } else {
            // Jika jadwal kosong tapi dia kunjungan mandiri, anggap tuntas 100%
            $karyawan->persen_target = ($karyawan->kunjungan_selesai > 0) ? 100 : 0;
        }

        // --- LOGIKA KOL 5 (Tetap menggunakan nama_nasabah sebagai kunci) ---
        $rencanaKOL5AO = \DB::table('data_kunjungan_adms')
            ->where('kode_ao', $kodeAO)
            ->where('kol', 5)
            ->distinct()
            ->pluck('nama_nasabah')->toArray();

        $mandiriKOL5AO = \DB::table('kunjungans')
            ->where('kode_ao', $kodeAO)
            ->where('kol', 5)
            ->whereNotIn('nama_nasabah', $rencanaKOL5AO)
            ->distinct()
            ->pluck('nama_nasabah')->toArray();

        $gabunganWajibKOL5 = array_unique(array_merge($rencanaKOL5AO, $mandiriKOL5AO));
        $totalWajibKOL5AO = count($gabunganWajibKOL5);
        
        $daftarNamaNasabahSelesai = array_unique($semuaKunjungan->pluck('nama_nasabah')->toArray());
        
        $selesaiKOL5AO = 0;
        foreach ($gabunganWajibKOL5 as $nama) {
            if (in_array($nama, $daftarNamaNasabahSelesai)) {
                $selesaiKOL5AO++;
            }
        }

        $karyawan->persen_kol5 = $totalWajibKOL5AO > 0 
            ? round(($selesaiKOL5AO / $totalWajibKOL5AO) * 100) 
            : 0;

        // Syarat Capai Target: 10 nasabah unik dan KOL 5 tuntas
        $karyawan->capai_target = ($karyawan->kunjungan_selesai >= 10 && $karyawan->persen_kol5 >= 100);
        
        return $karyawan;
    });

    // --- 2. LOGIKA AGREGAT NASIONAL ---
    $totalSelesai = $performaAO->sum('kunjungan_selesai');
    $totalBelum = max(0, $totalKunjungan - $totalSelesai);
    $aoSelesaiTarget = $performaAO->where('capai_target', true)->count();

    // Persentase Nasional (Dibatas 100% agar dashboard utama tidak aneh)
    $kpi_target_nasional = $totalKunjungan > 0 ? min(round(($totalSelesai / $totalKunjungan) * 100), 100) : 0;

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

    $kunjungsGrouped = \App\Models\Nasabah::with('karyawan')
        ->orderByRaw("kol = 5 DESC")
        ->get()
        ->groupBy('kode_ao');

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
        'pengajuan_ijin_count' => $pengajuan_ijin_count,
        'list_pengajuan' => $list_pengajuan,
        'total_gagal_global' => $total_gagal_global,
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
                $data = \DB::table('ijin_kunjungans')
                    // Join pertama untuk ambil nama AO yang ijin (lama)
                    ->leftJoin('karyawans as ao_lama', 'ijin_kunjungans.kode_ao', '=', 'ao_lama.kode_ao')
                    // Join kedua untuk ambil nama AO pengganti (baru) berdasarkan kolom ao_pengganti
                    ->leftJoin('karyawans as ao_baru', 'ijin_kunjungans.ao_pengganti', '=', 'ao_baru.kode_ao')
                    ->where('ijin_kunjungans.status', 'disetujui')
                    ->select(
                        'ijin_kunjungans.*',
                        'ao_lama.nama as nama_ao_lama',
                        'ao_baru.nama as nama_ao_baru'
                    )
                    ->get()
                    ->map(function($item) {
                        // Jika ao_pengganti ada isinya, gabungkan Kode - Nama
                        $infoAOBaru = null;
                        if (!empty($item->ao_pengganti)) {
                            $infoAOBaru = $item->ao_pengganti . ' - ' . ($item->nama_ao_baru ?? 'Nama tidak ditemukan');
                        }

                        return [
                            'info_1' => $item->kode_ao . ' - ' . ($item->nama_ao_lama ?? 'N/A'),
                            'info_2' => $item->alasan,
                            'info_3' => date('d-m-Y', strtotime($item->tanggal)),
                            'status' => 'Gagal Kunjungan',
                            'info_ao_baru' => $infoAOBaru // Key ini yang dicari JavaScript-mu
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

   public function reassignJadwal(Request $request) 
    {
        try {
            $ijin = \App\Models\IjinKunjungan::findOrFail($request->ijin_id);
            $targetAo = $request->ao_baru; 

            // 1. Ambil jadwal milik AO yang ijin
            $jadwal = \App\Models\DataKunjunganAdm::where('kode_ao', $ijin->kode_ao)->get();

            if ($jadwal->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => "0 jadwal ditemukan untuk AO {$ijin->kode_ao}"
                ]);
            }

            // 2. UPDATE KOLOM ao_pengganti agar tulisan "Belum dioper" hilang
            $ijin->update([
                'ao_pengganti' => $targetAo
            ]);

            // 3. Pindahkan jadwal ke AO baru
            foreach ($jadwal as $item) {
                $item->update(['kode_ao' => $targetAo]);
            }

            return response()->json([
                'success' => true,
                'message' => $jadwal->count() . " jadwal berhasil dioper ke AO $targetAo"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal Update: ' . $e->getMessage()
            ], 500);
        }
    }
}