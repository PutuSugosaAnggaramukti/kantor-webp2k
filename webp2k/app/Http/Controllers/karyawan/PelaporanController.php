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
        $keyword = $request->search;

        // 1. DAFTAR AO (Tabel Atas)
        // Hanya tampilkan AO yang SUDAH melapor di tabel 'kunjungans'
        $pelaporan_all = Karyawan::whereHas('realisasiKunjungan')
            ->with(['realisasiKunjungan' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->get();

        $pelaporan_all = $pelaporan_all->map(function ($karyawan) {
            // Ambil data laporan asli terbaru
            $karyawan->kunjungan_terbaru = $karyawan->realisasiKunjungan->first();
            return $karyawan;
        });

        // 2. DAFTAR NASABAH (Tabel Bawah)
        // Nasabah hanya muncul jika ada datanya di tabel 'kunjungans'
        // Gunakan relasi baru 'laporanSelesai' (lihat poin 2 di bawah)
       $nasabah_terkunjungi = Nasabah::whereHas('laporanSelesai', function($q) {
            // Kita paksa query ini hanya melihat tabel kunjungans
            $q->whereNotNull('no_nasabah');
        })
        ->when($keyword, function ($query) use ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('nasabah', 'like', "%{$keyword}%")
                ->orWhere('no_angsuran', 'like', "%{$keyword}%");
            });
        })
        ->with(['laporanSelesai.karyawan']) 
        ->orderBy('nasabah', 'asc')
        ->paginate(10)
        ->withQueryString();

        if ($request->ajax()) {
            return view('admin.partials.pelaporan', compact('pelaporan_all', 'nasabah_terkunjungi'))->render();
        }

        $dashboard = new \App\Http\Controllers\dashboard\DashboardAdminController();
        $data = $dashboard->getDashboardData();

        $data['content'] = view('admin.partials.pelaporan', compact('pelaporan_all', 'nasabah_terkunjungi'))->render();
        $data['page'] = 'pelaporan';
        $data['title'] = 'Pelaporan Kunjungan';

        return view('admin.datakaryawan', $data);
    }

    public function detailAo(Request $request, $id_ao)
    {
        $histori_ao = DataKunjunganAdm::where('karyawan_id', $id_ao)
            ->orWhere('kode_ao', $id_ao)
            ->orderBy('tanggal', 'desc')
            ->paginate(15)
            ->withQueryString();

        $ao = Karyawan::where('id', $id_ao)
            ->orWhere('kode_ao', $id_ao)
            ->first();

        // 1. Jika diklik melalui menu (lewat fungsi loadAdminPage / AJAX)
        if ($request->ajax()) {
            return view('admin.partials.pelaporan_detail', compact('histori_ao', 'ao'));
        }

        // 2. Jika di-refresh secara manual (Bukan AJAX)
        // Kita kembalikan layout utama 'datakaryawan' dan isi kontennya dengan detail
        return view('admin.datakaryawan', [
            'content' => view('admin.partials.pelaporan_detail', compact('histori_ao', 'ao'))->render()
        ]);
    }

    public function laporanNasabah($noAngsuran)
    {
        $nasabah = Nasabah::where('no_angsuran', $noAngsuran)->first();
        if (!$nasabah) {
            return response()->json(['error' => 'Nasabah tidak ditemukan'], 404);
        }

        $laporan = \App\Models\Kunjungan::where('no_nasabah', $noAngsuran)
            ->with('karyawan')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                $foto = [];
                if ($item->foto_kunjungan) {
                    $raw = is_string($item->foto_kunjungan) ? json_decode($item->foto_kunjungan, true) : $item->foto_kunjungan;
                    $foto = is_array($raw) ? $raw : [$raw];
                }
                return [
                    'id' => $item->id,
                    'kode_ao' => $item->kode_ao,
                    'nama_ao' => $item->karyawan->nama ?? '-',
                    'created_at' => \Carbon\Carbon::parse($item->created_at)->format('d-m-Y H:i'),
                    'ada_di_lokasi' => $item->ada_di_lokasi ?? '-',
                    'catatan' => $item->catatan ?? '-',
                    'koordinat' => $item->koordinat ?? '-',
                    'tgl_janji_bayar' => $item->tgl_janji_bayar ? \Carbon\Carbon::parse($item->tgl_janji_bayar)->format('d-m-Y') : '-',
                    'nominal_janji_bayar' => $item->nominal_janji_bayar ? 'Rp ' . number_format($item->nominal_janji_bayar, 0, ',', '.') : '-',
                    'bukti_transfer' => $item->bukti_transfer,
                    'foto' => $foto,
                ];
            });

        return response()->json([
            'nasabah' => [
                'no_angsuran' => $nasabah->no_angsuran,
                'nama' => $nasabah->nasabah,
                'alamat' => $nasabah->alamat ?? '-',
                'kol' => $nasabah->kol ?? '-',
            ],
            'laporan' => $laporan,
        ]);
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
