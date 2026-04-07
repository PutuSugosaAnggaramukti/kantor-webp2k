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
        $query->where('is_hb', 1);
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
    $dashboard = new \App\Http\Controllers\Dashboard\DashboardAdminController();
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
        // 1. Validasi Lengkap (Mencakup semua field baru dari form)
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
                'kode_ao'         => $request->kode_ao ?? '-',

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

    public function getDaftarNoAnggota()
    {
        // Gunakan try-catch untuk debugging jika terjadi error internal
        try {
            $nasabah = \App\Models\Nasabah::select('no_angsuran', 'nasabah', 'alamat', 'kol')->get();
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
            \App\Models\Nasabah::truncate();

            // PANGGILAN SANGAT PENTING: 
            // Jangan masukkan '1' di parameter pertama. Biarkan null.
            \Maatwebsite\Excel\Facades\Excel::import(
                new \App\Imports\NasabahImport(null, $labelBulan), 
                $request->file('file')
            );
            
            return redirect()->back()->with('success', 'Data Berhasil Diimport!');
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage(); 
        }
    }

    public function import_hb(Request $request)
    {
        $request->validate([
            // Tambahkan txt agar CSV yang terbaca sebagai plain text tetap lolos
            'file_excel' => 'required|mimes:xlsx,xls,csv,txt',
        ]);

        try {
            Excel::import(new NasabahHBImport, $request->file('file_excel'));
            return back()->with('success', 'Data Nasabah HB berhasil diimport!');
        } catch (\Exception $e) {
            // Ini akan memunculkan pesan error spesifik jika ada kolom yang salah
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
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
