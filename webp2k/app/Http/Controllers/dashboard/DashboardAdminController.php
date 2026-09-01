<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\DataKunjunganAdm;
use App\Models\IjinKunjungan;
use App\Models\Kunjungan;
use App\Models\StatistikBulanan;
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

    public function statsJson()
    {
        $bulanAktif = now()->format('Y-m');
        $stat = $this->hitungStatistikBulan($bulanAktif);

        return response()->json([
            'bulan' => $bulanAktif,
            'total_rencana' => $stat['total_rencana'],
            'sudah_dikunjungi' => $stat['sudah_dikunjungi'],
            'belum_dikunjungi' => $stat['belum_dikunjungi'],
            'total_gagal' => $stat['total_gagal'],
        ]);
    }

    public function getPerformaBulan($bulan)
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            return response()->json(['error' => 'Format bulan tidak valid'], 400);
        }

        [$tahun, $bulanAngka] = explode('-', $bulan);

        $totalKunjungan = \DB::table('data_kunjungan_adms')->where('bulan', $bulan)->count();

        $sudahKunjungNo = \DB::table('kunjungans')
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulanAngka)
            ->pluck('no_nasabah')->toArray();

        $totalSelesai = \DB::table('data_kunjungan_adms')
            ->where('bulan', $bulan)
            ->whereIn('no_angsuran', $sudahKunjungNo)
            ->count();

        $kpi = $totalKunjungan > 0 ? min(round(($totalSelesai / $totalKunjungan) * 100), 100) : 0;

        $performaAO = \App\Models\Karyawan::where('status', 'aktif')->get()->map(function ($karyawan) use ($bulan, $tahun, $bulanAngka) {
            $kodeAO = trim($karyawan->kode_ao);

            $kunjunganSelesai = \DB::table('kunjungans')
                ->where('kode_ao', $kodeAO)
                ->whereYear('created_at', $tahun)
                ->whereMonth('created_at', $bulanAngka)
                ->distinct()->count('no_nasabah');

            $rencanaAO = \DB::table('data_kunjungan_adms')
                ->where('kode_ao', $kodeAO)
                ->where('bulan', $bulan)
                ->distinct()->count('no_angsuran');

            $persenTarget = $rencanaAO > 0 ? round(min(($kunjunganSelesai / $rencanaAO) * 100, 100)) : ($kunjunganSelesai > 0 ? 100 : 0);

            $rencanaKOL5 = \DB::table('data_kunjungan_adms')
                ->where('kode_ao', $kodeAO)->where('bulan', $bulan)->where('kol', 5)
                ->distinct()->pluck('nama_nasabah')->toArray();
            $mandiriKOL5 = \DB::table('kunjungans')
                ->where('kode_ao', $kodeAO)->where('kol', 5)
                ->whereYear('created_at', $tahun)->whereMonth('created_at', $bulanAngka)
                ->whereNotIn('nama_nasabah', $rencanaKOL5)
                ->distinct()->pluck('nama_nasabah')->toArray();
            $gabungan = array_unique(array_merge($rencanaKOL5, $mandiriKOL5));
            $totalWajib = count($gabungan);
            $namaSelesai = \DB::table('kunjungans')
                ->where('kode_ao', $kodeAO)
                ->whereYear('created_at', $tahun)->whereMonth('created_at', $bulanAngka)
                ->pluck('nama_nasabah')->toArray();
            $selesaiKOL5 = 0;
            foreach ($gabungan as $nama) { if (in_array($nama, $namaSelesai)) $selesaiKOL5++; }
            $persenKOL5 = $totalWajib > 0 ? round(($selesaiKOL5 / $totalWajib) * 100) : 0;

            return (object)[
                'nama' => $karyawan->nama,
                'persen_target' => $persenTarget,
                'persen_kol5' => $persenKOL5,
            ];
        });

        return response()->json([
            'bulan' => $bulan,
            'label' => \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y'),
            'kpi' => $kpi,
            'total_kunjungan' => $totalKunjungan,
            'total_selesai' => $totalSelesai,
            'performa_ao' => $performaAO,
        ]);
    }
    
  public function getDashboardData()
{
    // --- SNAPSHOT OTOMATIS STATISTIK BULANAN (bulan-bulan lalu) ---
    $this->pastikanSnapshotBulanan();

    // --- STATISTIK KARTU: TAMPILKAN BULAN BERJALAN (reset 0 tiap awal bulan) ---
    $bulanAktif = now()->format('Y-m');
    $statBulanIni = $this->hitungStatistikBulan($bulanAktif);
    $totalKunjungan = $statBulanIni['total_rencana'];
    $totalSelesai = $statBulanIni['sudah_dikunjungi'];
    $totalBelum = $statBulanIni['belum_dikunjungi'];
    $total_gagal_global = $statBulanIni['total_gagal'];

    // --- 1. DATA DASAR ---
    $totalKunjunganAll = \App\Models\DataKunjunganAdm::count(); 

    // --- LOGIKA GAGAL KUNJUNGAN (IJIN DISETUJUI) ---
    $list_gagal_kunjungan_all = \App\Models\IjinKunjungan::with('karyawan')
        ->whereIn('status', ['disetujui', 'DISETUJUI']) 
        ->orderBy('tanggal', 'desc')
        ->get();

    $total_gagal_all = $list_gagal_kunjungan_all->count();
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
    [$tahunAktif, $bulanAngkaAktif] = explode('-', $bulanAktif);
    $performaAO = \App\Models\Karyawan::where('status', 'aktif')->get()->map(function ($karyawan) use ($bulanAktif, $tahunAktif, $bulanAngkaAktif) {
        $kodeAO = trim($karyawan->kode_ao);
        
        // 1. Kunjungan unik bulan ini
        $kunjunganUnik = \DB::table('kunjungans')
            ->where('kode_ao', $kodeAO)
            ->whereYear('created_at', $tahunAktif)
            ->whereMonth('created_at', $bulanAngkaAktif)
            ->distinct()
            ->pluck('no_nasabah') 
            ->toArray();

        $karyawan->kunjungan_selesai = count($kunjunganUnik);

        // Kunjungan bulan ini (untuk KOL 5: perlu nama_nasabah)
        $kunjunganBulanIni = \DB::table('kunjungans')
            ->where('kode_ao', $kodeAO)
            ->whereYear('created_at', $tahunAktif)
            ->whereMonth('created_at', $bulanAngkaAktif)
            ->get();

        // 2. HITUNG TARGET (Jadwal Unik bulan ini)
        $rencanaAO = \DB::table('data_kunjungan_adms')
            ->where('kode_ao', $kodeAO)
            ->where('bulan', $bulanAktif)
            ->distinct()
            ->count('no_angsuran');

        // 3. PERSENTASE TARGET (bulanan)
        if ($rencanaAO > 0) {
            $persenRaw = ($karyawan->kunjungan_selesai / $rencanaAO) * 100;
            $karyawan->persen_target = round(min($persenRaw, 100)); 
        } else {
            $karyawan->persen_target = ($karyawan->kunjungan_selesai > 0) ? 100 : 0;
        }

        // --- LOGIKA KOL 5 (bulanan) ---
        $rencanaKOL5AO = \DB::table('data_kunjungan_adms')
            ->where('kode_ao', $kodeAO)
            ->where('bulan', $bulanAktif)
            ->where('kol', 5)
            ->distinct()
            ->pluck('nama_nasabah')->toArray();

        $mandiriKOL5AO = \DB::table('kunjungans')
            ->where('kode_ao', $kodeAO)
            ->where('kol', 5)
            ->whereYear('created_at', $tahunAktif)
            ->whereMonth('created_at', $bulanAngkaAktif)
            ->whereNotIn('nama_nasabah', $rencanaKOL5AO)
            ->distinct()
            ->pluck('nama_nasabah')->toArray();

        $gabunganWajibKOL5 = array_unique(array_merge($rencanaKOL5AO, $mandiriKOL5AO));
        $totalWajibKOL5AO = count($gabunganWajibKOL5);
        
        $daftarNamaNasabahSelesai = array_unique($kunjunganBulanIni->pluck('nama_nasabah')->toArray());
        
        $selesaiKOL5AO = 0;
        foreach ($gabunganWajibKOL5 as $nama) {
            if (in_array($nama, $daftarNamaNasabahSelesai)) {
                $selesaiKOL5AO++;
            }
        }

        $karyawan->persen_kol5 = $totalWajibKOL5AO > 0 
            ? round(($selesaiKOL5AO / $totalWajibKOL5AO) * 100) 
            : 0;

        // --- ALL-TIME (semua bulan) ---
        $kunjunganAllTime = \DB::table('kunjungans')
            ->where('kode_ao', $kodeAO)
            ->distinct()
            ->pluck('no_nasabah') 
            ->toArray();
        $karyawan->kunjungan_selesai_alltime = count($kunjunganAllTime);

        $rencanaAOAllTime = \DB::table('data_kunjungan_adms')
            ->where('kode_ao', $kodeAO)
            ->distinct()
            ->count('no_angsuran');

        $karyawan->persen_target_alltime = $rencanaAOAllTime > 0
            ? round(min(($karyawan->kunjungan_selesai_alltime / $rencanaAOAllTime) * 100, 100))
            : ($karyawan->kunjungan_selesai_alltime > 0 ? 100 : 0);

        // KOL 5 all-time
        $rencanaKOL5AllTime = \DB::table('data_kunjungan_adms')
            ->where('kode_ao', $kodeAO)
            ->where('kol', 5)
            ->distinct()
            ->pluck('nama_nasabah')->toArray();

        $mandiriKOL5AllTime = \DB::table('kunjungans')
            ->where('kode_ao', $kodeAO)
            ->where('kol', 5)
            ->whereNotIn('nama_nasabah', $rencanaKOL5AllTime)
            ->distinct()
            ->pluck('nama_nasabah')->toArray();

        $gabunganKOL5AllTime = array_unique(array_merge($rencanaKOL5AllTime, $mandiriKOL5AllTime));
        $totalWajibKOL5AllTime = count($gabunganKOL5AllTime);

        $namaSelesaiAllTime = array_unique(\DB::table('kunjungans')
            ->where('kode_ao', $kodeAO)
            ->pluck('nama_nasabah')->toArray());

        $selesaiKOL5AllTime = 0;
        foreach ($gabunganKOL5AllTime as $nama) {
            if (in_array($nama, $namaSelesaiAllTime)) $selesaiKOL5AllTime++;
        }

        $karyawan->persen_kol5_alltime = $totalWajibKOL5AllTime > 0
            ? round(($selesaiKOL5AllTime / $totalWajibKOL5AllTime) * 100)
            : 0;

        // Syarat Capai Target: 10 nasabah unik dan KOL 5 tuntas
        $karyawan->capai_target = ($karyawan->kunjungan_selesai >= 10 && $karyawan->persen_kol5 >= 100);
        
        return $karyawan;
    });

    // --- 2. LOGIKA AGREGAT NASIONAL ---
    $totalSelesaiAll = $performaAO->sum('kunjungan_selesai');
    $totalBelumAll = max(0, $totalKunjungan - $totalSelesaiAll);
    $aoSelesaiTarget = $performaAO->where('capai_target', true)->count();

    // Persentase Nasional Bulanan = Sudah / Total Kunjungan (bulan ini)
    $kpi_target_nasional = $totalKunjungan > 0 ? min(round(($totalSelesaiAll / $totalKunjungan) * 100), 100) : 0;

    // Persentase Nasional Semua Bulan (all-time)
    $totalSelesaiAllTime = $performaAO->sum('kunjungan_selesai_alltime');
    $totalJadwalAllTime = \App\Models\DataKunjunganAdm::count();
    $kpi_target_semua_bulan = $totalJadwalAllTime > 0 ? min(round(($totalSelesaiAllTime / $totalJadwalAllTime) * 100), 100) : 0;

    $target_kol5_nama = \DB::table('data_kunjungan_adms')
        ->where('bulan', $bulanAktif)
        ->where('kol', 5)
        ->pluck('nama_nasabah')->toArray();
    $mandiri_kol5_nama = \DB::table('kunjungans')
        ->where('kol', 5)
        ->whereYear('created_at', $tahunAktif)
        ->whereMonth('created_at', $bulanAngkaAktif)
        ->whereNotIn('nama_nasabah', $target_kol5_nama)
        ->pluck('nama_nasabah')->toArray();
    $gabungan_kol5_nasional = array_unique(array_merge($target_kol5_nama, $mandiri_kol5_nama));
    $total_wajib_kol5 = count($gabungan_kol5_nasional);

    $nama_sudah_visit = \DB::table('kunjungans')
        ->whereYear('created_at', $tahunAktif)
        ->whereMonth('created_at', $bulanAngkaAktif)
        ->pluck('nama_nasabah')->toArray();
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

    // --- RIWAYAT STATISTIK BULANAN (arsip bulan lalu yang bisa di-download) ---
    $riwayatStatistik = StatistikBulanan::orderBy('bulan', 'desc')->get();

    // --- DAFTAR BULAN untuk dropdown performa ---
    $daftarBulan = \DB::table('data_kunjungan_adms')
        ->whereNotNull('bulan')
        ->where('bulan', '!=', '')
        ->selectRaw("DISTINCT bulan")
        ->orderBy('bulan', 'desc')
        ->pluck('bulan')
        ->toArray();

    return [
        'totalKunjungan' => $totalKunjungan,
        'totalSelesai' => $totalSelesai,
        'totalBelum' => $totalBelum,
        'bulanAktif' => $bulanAktif,
        'riwayatStatistik' => $riwayatStatistik,
        'aoSelesaiTarget' => $aoSelesaiTarget,
        'labels' => $labels,
        'counts' => $counts,
        'kpi_target_nasional' => $kpi_target_nasional,
        'kpi_target_semua_bulan' => $kpi_target_semua_bulan,
        'kpi_kol5_nasional' => $kpi_kol5_nasional,
        'total_wajib_kol5' => $total_wajib_kol5,
        'detailPerformaAO' => $performaAO,
        'kunjungansGrouped' => $kunjungsGrouped,
        'pengajuan_ijin_count' => $pengajuan_ijin_count,
        'list_pengajuan' => $list_pengajuan,
        'total_gagal_global' => $total_gagal_global,
        'daftarBulan' => $daftarBulan,
    ];
}

    public function hitungStatistikBulan($bulan)
    {
        [$tahun, $bulanAngka] = array_pad(explode('-', $bulan), 2, null);
        $tahun = (int) $tahun;
        $bulanAngka = (int) $bulanAngka;

        // 1. Total Kunjungan: semua baris jadwal pada bulan tersebut
        $totalRencana = \DB::table('data_kunjungan_adms')
            ->where('bulan', $bulan)
            ->count();

        // 2. Sudah Dikunjungi: jadwal yang no_angsuran-nya sudah ada di kunjungans bulan tsb
        $sudahKunjungNo = \DB::table('kunjungans')
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulanAngka)
            ->pluck('no_nasabah')
            ->toArray();

        $sudahDikunjungi = \DB::table('data_kunjungan_adms')
            ->where('bulan', $bulan)
            ->whereIn('no_angsuran', $sudahKunjungNo)
            ->count();

        // 3. Belum Dikunjungi: jadwal yang no_angsuran-nya belum ada di kunjungans
        $belumDikunjungi = max(0, $totalRencana - $sudahDikunjungi);

        // 4. Total Gagal Kunjungan: ijin AO disetujui pada bulan tersebut
        $totalGagal = \DB::table('ijin_kunjungans')
            ->whereIn('status', ['disetujui', 'DISETUJUI'])
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulanAngka)
            ->count();

        return [
            'total_rencana' => $totalRencana,
            'sudah_dikunjungi' => $sudahDikunjungi,
            'belum_dikunjungi' => $belumDikunjungi,
            'total_gagal' => $totalGagal,
        ];
    }

   public function pastikanSnapshotBulanan()
    {
        $bulanIni = now()->format('Y-m');

        // Kumpulkan bulan-bulan yang sudah memiliki data (jadwal / kunjungan / ijin)
        $bulanData = collect();

        $bulanData = $bulanData->merge(
            \DB::table('data_kunjungan_adms')
                ->whereNotNull('bulan')
                ->where('bulan', '!=', '')
                ->distinct()
                ->pluck('bulan')
        );

        $bulanData = $bulanData->merge(
            \DB::table('kunjungans')
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bulan")
                ->distinct()
                ->pluck('bulan')
        );

        $bulanData = $bulanData->merge(
            \DB::table('ijin_kunjungans')
                ->whereIn('status', ['disetujui', 'DISETUJUI'])
                ->selectRaw("DATE_FORMAT(tanggal, '%Y-%m') as bulan")
                ->distinct()
                ->pluck('bulan')
        );

        // Snapshot semua bulan yang sudah lewat (kurang dari bulan berjalan) dan belum tersimpan
        foreach ($bulanData->unique()->filter() as $bulan) {
            if ($bulan >= $bulanIni) continue; // bulan berjalan/masa depan: jangan arsipkan

            $stat = $this->hitungStatistikBulan($bulan);
            $snapshot = StatistikBulanan::where('bulan', $bulan)->first();
            if ($snapshot) {
                // Refresh snapshot yang sudah ada agar mengikuti logika hitung terbaru
                $snapshot->total_rencana = $stat['total_rencana'];
                $snapshot->sudah_dikunjungi = $stat['sudah_dikunjungi'];
                $snapshot->belum_dikunjungi = $stat['belum_dikunjungi'];
                $snapshot->total_gagal = $stat['total_gagal'];
                $snapshot->save();
                continue;
            }

            StatistikBulanan::create([
                'bulan' => $bulan,
                'total_rencana' => $stat['total_rencana'],
                'sudah_dikunjungi' => $stat['sudah_dikunjungi'],
                'belum_dikunjungi' => $stat['belum_dikunjungi'],
                'total_gagal' => $stat['total_gagal'],
            ]);
        }
    }

   public function getDetail($type, Request $request)
    {
        try {
            $data = [];
            $bulan = $request->query('bulan', now()->format('Y-m'));
            [$tahun, $bulanAngka] = array_pad(explode('-', $bulan), 2, null);
            $tahun = (int) $tahun;
            $bulanAngka = (int) $bulanAngka;

            if ($type == 'rencana') {
                $sudahKunjungNo = \DB::table('kunjungans')
                    ->whereYear('created_at', $tahun)
                    ->whereMonth('created_at', $bulanAngka)
                    ->pluck('no_nasabah')
                    ->toArray();

                $data = \DB::table('data_kunjungan_adms')
                    ->join('karyawans', 'data_kunjungan_adms.kode_ao', '=', 'karyawans.kode_ao')
                    ->where('data_kunjungan_adms.bulan', $bulan)
                    ->select(
                        'data_kunjungan_adms.kode_ao',
                        'karyawans.nama as nama_ao',
                        'data_kunjungan_adms.nama_nasabah',
                        'data_kunjungan_adms.no_angsuran',
                        'data_kunjungan_adms.tanggal'
                    )
                    ->orderByDesc('data_kunjungan_adms.tanggal')
                    ->get()->map(function($item) use ($sudahKunjungNo) {
                        return [
                            'info_1' => $item->kode_ao . ' - ' . $item->nama_ao,
                            'info_2' => $item->nama_nasabah,
                            'info_3' => date('d-m-Y', strtotime($item->tanggal)),
                            'status' => 'Rencana',
                            'keterangan' => in_array($item->no_angsuran, $sudahKunjungNo) ? 'Sudah Dikunjungi' : 'Belum Dikunjungi'
                        ];
                    });

            } elseif ($type == 'selesai') {
                $sudahKunjungNo = \DB::table('kunjungans')
                    ->whereYear('created_at', $tahun)
                    ->whereMonth('created_at', $bulanAngka)
                    ->pluck('no_nasabah')
                    ->toArray();

                $data = \DB::table('data_kunjungan_adms')
                    ->join('karyawans', 'data_kunjungan_adms.kode_ao', '=', 'karyawans.kode_ao')
                    ->where('data_kunjungan_adms.bulan', $bulan)
                    ->whereIn('data_kunjungan_adms.no_angsuran', $sudahKunjungNo)
                    ->select(
                        'data_kunjungan_adms.kode_ao',
                        'karyawans.nama as nama_ao',
                        'data_kunjungan_adms.nama_nasabah',
                        'data_kunjungan_adms.tanggal'
                    )
                    ->orderByDesc('data_kunjungan_adms.tanggal')
                    ->get()->map(function($item) {
                        return [
                            'info_1' => $item->kode_ao . ' - ' . $item->nama_ao,
                            'info_2' => $item->nama_nasabah,
                            'info_3' => date('d-m-Y', strtotime($item->tanggal)),
                            'status' => 'Sudah Dikunjungi'
                        ];
                    });

            } elseif ($type == 'belum') {
                $sudahKunjung = \DB::table('kunjungans')
                    ->whereYear('created_at', $tahun)
                    ->whereMonth('created_at', $bulanAngka)
                    ->pluck('no_nasabah')
                    ->toArray();
                
                $data = \DB::table('data_kunjungan_adms')
                    ->join('karyawans', 'data_kunjungan_adms.kode_ao', '=', 'karyawans.kode_ao')
                    ->where('data_kunjungan_adms.bulan', $bulan)
                    ->whereNotIn('data_kunjungan_adms.no_angsuran', $sudahKunjung)
                    ->select(
                        'data_kunjungan_adms.kode_ao',
                        'karyawans.nama as nama_ao',
                        'data_kunjungan_adms.nama_nasabah',
                        'data_kunjungan_adms.tanggal'
                    )
                    ->orderByDesc('data_kunjungan_adms.tanggal')
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
                    ->whereIn('ijin_kunjungans.status', ['disetujui', 'DISETUJUI'])
                    ->whereYear('ijin_kunjungans.tanggal', $tahun)
                    ->whereMonth('ijin_kunjungans.tanggal', $bulanAngka)
                    ->select(
                        'ijin_kunjungans.*',
                        'ao_lama.nama as nama_ao_lama',
                        'ao_baru.nama as nama_ao_baru'
                    )
                    ->orderByDesc('ijin_kunjungans.tanggal')
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

   public function exportStatistikBulanan($bulan = null)
    {
        if ($bulan) {
            // Per bulan: file detail nasabah (AO, Nama Nasabah, Tanggal, Status)
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\StatistikBulananDetailExport($bulan),
                'Statistik_Bulanan_' . $bulan . '.xlsx'
            );
        }

        // Semua bulan: rekap agregat per bulan
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\StatistikBulananExport(),
            'Statistik_Bulanan_Riwayat.xlsx'
        );
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