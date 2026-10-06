<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use App\Exports\NasabahExport;
use App\Imports\NasabahImport;
use App\Imports\NasabahHBImport;  
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Nasabah;
use App\Models\DataKunjunganAdm;
use Illuminate\Http\Request;

class NasabahController extends Controller
{

  public function nasabahContent(Request $request)
{
    $activeTab = $request->query('tab', '1'); 
    $search = $request->query('search'); 

    // Urutkan berdasarkan abjad nama nasabah
    $query = \App\Models\Nasabah::orderBy('nasabah', 'asc');

    // 1. Logika Filter Tab
    if ($activeTab === 'hb') {
        $query->where('is_hb', 1)->with('ao');
    } else {
        // Pastikan data yang muncul bukan data HB
        $targetKol = in_array($activeTab, ['1', '2', '3', '4', '5']) ? $activeTab : '1';
        $query->where('kol', $targetKol)->where('is_hb', 0);
    }

    // 2. Logika Pencarian
    if (!empty($search)) {
        $query->where(function($q) use ($search) {
            $q->where('nasabah', 'LIKE', "%$search%")
              ->orWhere('no_angsuran', 'LIKE', "%$search%")
              ->orWhere('alamat', 'LIKE', "%$search%");
        });
    }

    $nasabah_all = $query->paginate(10)->withQueryString();

    // 3. Optimasi Count (Hitung sekaligus data non-HB)
    $counts = \App\Models\Nasabah::selectRaw("
            SUM(CASE WHEN kol = '1' AND is_hb = 0 THEN 1 ELSE 0 END) as c1,
            SUM(CASE WHEN kol = '2' AND is_hb = 0 THEN 1 ELSE 0 END) as c2,
            SUM(CASE WHEN kol = '3' AND is_hb = 0 THEN 1 ELSE 0 END) as c3,
            SUM(CASE WHEN kol = '4' AND is_hb = 0 THEN 1 ELSE 0 END) as c4,
            SUM(CASE WHEN kol = '5' AND is_hb = 0 THEN 1 ELSE 0 END) as c5,
            SUM(CASE WHEN is_hb = 1 THEN 1 ELSE 0 END) as chb
        ")->first();

    $viewData = [
        'nasabah_all' => $nasabah_all,
        'activeTab'   => $activeTab,
        'search'      => $search,
        'count1'      => $counts->c1 ?? 0,
        'count2'      => $counts->c2 ?? 0,
        'count3'      => $counts->c3 ?? 0,
        'count4'      => $counts->c4 ?? 0,
        'count5'      => $counts->c5 ?? 0,
        'countHB'     => $counts->chb ?? 0,
    ];

    // Response untuk AJAX
    if ($request->ajax()) {
        return view('admin.partials.nasabah_table', $viewData)->render();
    }

    // 4. Response untuk Full Page Load
    $dashboard = new \App\Http\Controllers\dashboard\DashboardAdminController();
    $data = $dashboard->getDashboardData();
    
    $data = array_merge($data, $viewData); 
    $data['content'] = view('admin.partials.nasabah_table', $viewData)->render();
    $data['page']    = 'nasabah';
    $data['title']   = 'Data Nasabah';

    return view('admin.datakaryawan', $data);
}

    public function detail($no_angsuran)
    {
        $histori_kunjungan = DataKunjunganAdm::where('no_angsuran', $no_angsuran)
            ->with('karyawan')
            ->get();

        return view('admin.partials.pengunjung_nasabah', compact('histori_kunjungan'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Lengkap
        $request->validate([
            'no_angsuran' => 'required|unique:nasabahs,no_angsuran',
            'nasabah'     => 'required',
            'alamat'      => 'required',
            'kol'         => 'required',
            'nominal'     => 'nullable|numeric',
            'sisa_pokok'  => 'nullable|numeric',
            'tgl_pinjam'  => 'nullable|date',
            'tgl_jt'      => 'nullable|date',
            'lama'        => 'nullable|numeric',
        ], [
            'no_angsuran.unique' => 'Nomor Anggota ini sudah terdaftar di sistem.',
            'no_angsuran.required' => 'Nomor Anggota wajib diisi.',
            'nasabah.required' => 'Nama Nasabah wajib diisi.',
            'kol.required' => 'Klasifikasi nasabah wajib dipilih.',
        ]);

        try {
            \App\Models\Nasabah::create([
                // --- KOLOM 1: Identitas ---
                'kode'            => $request->kode ?? '-',
                'no_angsuran'     => $request->no_angsuran,
                'rekening_kredit' => $request->rekening_kredit ?? '-',
                'kode_nasabah'    => $request->kode_nasabah ?? '-',
                'nasabah'         => $request->nasabah,
                'alamat'          => $request->alamat,

                // --- KOLOM 2: Tenor & Keuangan ---
                'lama'            => $request->lama ?? 0,
                'tgl_pinjam'      => $request->tgl_pinjam,
                'tgl_jt'          => $request->tgl_jt,
                'nominal'         => $request->nominal ?? 0,
                'sisa_pokok'      => $request->sisa_pokok ?? 0,
                'pokok_per_bulan' => $request->pokok_per_bulan ?? 0,
                'bunga_per_bulan' => $request->bunga_per_bulan ?? 0,
                
                // DISESUAIKAN: Menggunakan nama kolom baru hasil migrasi (AO-020 dkk)
                'kode_ao_nasabah' => $request->kode_ao ?? '-', 

                // --- KOLOM 3: Tunggakan & Kualitas ---
                'tunggakan_pokok' => $request->tunggakan_pokok ?? 0,
                'hari_pokok'      => $request->hari_pokok ?? 0,
                'tunggakan_bunga' => $request->tunggakan_bunga ?? 0,
                'hari_bunga'      => $request->hari_bunga ?? 0,
                'denda'           => $request->denda ?? 0,
                'kol'             => $request->kol,

                // --- Default System ---
                'nama_ao'         => '-', 
                'sudah_kunjung'   => 0,
                'is_hb'           => 0,
                'bulan'           => now()->format('Y-m'),
            ]);

            return response()->json([
                'success' => 'Nasabah ' . $request->nasabah . ' berhasil ditambahkan ke Klasifikasi KOL ' . $request->kol
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Gagal Simpan Nasabah Manual: " . $e->getMessage());
            return response()->json([
                'errors' => ['db' => ['Gagal menyimpan ke database: ' . $e->getMessage()]]
            ], 500);
        }
    }

  public function getDaftarNoAnggota(Request $request)
    {
        try {
            $search = $request->q;
            
            // Ambil data menggunakan Model Nasabah
            $query = \App\Models\Nasabah::select('no_angsuran', 'nasabah', 'alamat', 'kol', 'kode');

            // 1. Logika Pencarian (Jika User Mengetik)
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('no_angsuran', 'LIKE', "%$search%")
                    ->orWhere('nasabah', 'LIKE', "%$search%");
                });
            }
            
            $nasabah = $query->orderBy('nasabah', 'asc')
                ->limit(20) // Batasi agar respons cepat
                ->get()
                ->map(function($item) {
                    // Return format yang dibutuhkan Select2 AJAX
                    return [
                        'id'    => $item->no_angsuran,
                        'text'  => $item->no_angsuran . " - " . $item->nasabah,
                        'nasabah' => $item->nasabah,
                        'alamat'  => $item->alamat,
                        'kol'     => $item->kol,
                        'kode'    => $item->kode
                    ];
                });

            return response()->json($nasabah);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getNasabah($no_angsuran)
    {
        $nasabah = Nasabah::where('no_angsuran', $no_angsuran)->first();

        if ($nasabah) {
            return response()->json([
                'success' => true,
                'data'    => $nasabah 
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Data tidak ditemukan'
        ]);
    }

    public function importExcel(Request $request) 
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

        try {
            $labelBulan = date('F Y');

            // Hapus hanya data reguler (non-HB) supaya data HB tidak ikut hilang
            // saat user meng-upload data reguler setelah import HB.
            $jumlahHb = \App\Models\Nasabah::where('is_hb', 1)->count();
            \App\Models\Nasabah::where('is_hb', 0)->delete();
            \App\Imports\NasabahImport::$hbDilewati = 0;

            \Maatwebsite\Excel\Facades\Excel::import(
                new \App\Imports\NasabahImport(null, $labelBulan), 
                $request->file('file')
            );

            $pesan = 'Data Berhasil Diimport!';
            if ($jumlahHb > 0) {
                $pesan .= " ({$jumlahHb} nasabah HB dipertahankan";
                if (\App\Imports\NasabahImport::$hbDilewati > 0) {
                    $pesan .= ', ' . \App\Imports\NasabahImport::$hbDilewati
                        . ' baris di file dilewati karena sudah data HB';
                }
                $pesan .= ')';
            }

            return redirect()->back()->with('success', $pesan);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function import_hb(Request $request)
    {
        $request->validate([
            // Tambahkan txt agar CSV yang terbaca sebagai plain text tetap lolos
            'file_excel' => 'required|mimes:xlsx,xls,csv,txt',
        ]);

        try {
            // 1) Perbaiki data HB lama yang kolomnya bergeser (ada kolom ID di file)
            $diperbaiki = $this->perbaikiHbGeser();

            // 2) Import file terbaru
            $import = new NasabahHBImport;
            \App\Imports\NasabahHBImport::$barisGeser = 0;
            Excel::import($import, $request->file('file_excel'));

            $pesan = 'Data Nasabah HB berhasil diimport!';
            if (\App\Imports\NasabahHBImport::$barisGeser > 0) {
                $pesan .= ' ' . \App\Imports\NasabahHBImport::$barisGeser
                    . ' baris terdeteksi ada kolom ID di depan, sudah digeser otomatis.';
            }
            if ($diperbaiki > 0) {
                $pesan .= " {$diperbaiki} data HB lama yang kolomnya bergeser sudah diperbaiki.";
            }
            if (!empty($import->kodeAoTidakDikenal)) {
                $pesan .= ' Kode AO belum terdaftar di data karyawan: '
                    . implode(', ', $import->kodeAoTidakDikenal)
                    . ' (kolom AO tampil "-").';
            }

            return back()->with('success', $pesan);
        } catch (\Exception $e) {
            // Ini akan memunculkan pesan error spesifik jika ada kolom yang salah
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /**
     * Perbaiki baris HB hasil import lama yang bergeser 1 kolom karena file
     * punya kolom ID di paling kiri. Ciri: nama nasabah berupa angka murni
     * (padahal tidak mungkin) dan no. angsuran berisi kode (berhuruf).
     */
    private function perbaikiHbGeser(): int
    {
        $korup = \App\Models\Nasabah::where('is_hb', 1)
            ->where('nasabah', 'REGEXP', '^[0-9]+$')
            ->where('no_angsuran', 'NOT REGEXP', '^[0-9]+$')
            ->get();

        $diperbaiki = 0;

        foreach ($korup as $r) {
            $kunciBaru = trim($r->nasabah);   // no. angsuran asli (tersimpan di kolom nasabah)
            $kodeAsli  = trim($r->no_angsuran); // kode asli (tersimpan di kolom no_angsuran)

            // Nama asli tersimpan di kolom alamat, kadang masih menyatu "NAMA/ALAMAT"
            $namaBaru   = trim((string) $r->alamat);
            $alamatBaru = '-';
            if ($namaBaru !== '' && strpos($namaBaru, '/') !== false) {
                $bagi      = explode('/', $namaBaru, 2);
                $namaBaru  = trim($bagi[0]);
                $alamatBaru = trim($bagi[1]) ?: '-';
            }
            if ($namaBaru === '' || $namaBaru === '-' || preg_match('/^[0-9]+$/', $namaBaru)) {
                $namaBaru = '-';
            }

            // Plafon & Baki Debet ikut bergeser satu kolom
            $plafonBaru = (float) $r->bakidebet;
            $bakiBaru   = is_numeric($r->kode_ao_nasabah) ? (float) $r->kode_ao_nasabah : 0.0;
            if ($bakiBaru <= 0) $bakiBaru = $plafonBaru;

            // Tabrakan kunci: buang baris lama, biar import berikutnya menulis ulang
            if (\App\Models\Nasabah::where('no_angsuran', $kunciBaru)->exists()) {
                $r->delete();
                $diperbaiki++;
                continue;
            }

            \App\Models\Nasabah::where('no_angsuran', $r->no_angsuran)->update([
                'no_angsuran'     => $kunciBaru,
                'kode'            => $kodeAsli,
                'nasabah'         => $namaBaru,
                'alamat'          => $alamatBaru,
                'nominal'         => $plafonBaru,
                'sisa_pokok'      => $bakiBaru,
                'bakidebet'       => $bakiBaru,
                'kode_ao_nasabah' => null, // kolom asli tidak ikut tersimpan, nanti terisi dari file
            ]);

            $diperbaiki++;
        }

        return $diperbaiki;
    }

  public function exportExcel(Request $request)
    {
        $tglAwal = $request->query('tanggal_awal');
        $tglAkhir = $request->query('tanggal_akhir');

        if (!$tglAwal || !$tglAkhir) {
            return back()->with('error', 'Silakan pilih rentang tanggal.');
        }

        $nama_file = 'Data_Nasabah_' . $tglAwal . '_sd_' . $tglAkhir . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(new NasabahExport($tglAwal, $tglAkhir), $nama_file);
    }
}
