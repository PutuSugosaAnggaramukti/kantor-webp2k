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

        $query = \App\Models\Nasabah::orderBy('nasabah', 'asc');

        // 1. Logika Filter Tab
        if ($activeTab === 'hb') {
            $query->where('is_hb', 1);
        } elseif ($activeTab === '5') {
            $query->where('kol', '5')->where('is_hb', 0);
        } else {
            $targetKol = in_array($activeTab, ['1', '2', '3', '4']) ? $activeTab : '1';
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

        // 3. Siapkan Data untuk View
        $viewData = [
            'nasabah_all' => $nasabah_all,
            'activeTab'   => $activeTab,
            'search'      => $search,
            'count1'      => \App\Models\Nasabah::where('kol', '1')->where('is_hb', 0)->count(),
            'count2'      => \App\Models\Nasabah::where('kol', '2')->where('is_hb', 0)->count(),
            'count3'      => \App\Models\Nasabah::where('kol', '3')->where('is_hb', 0)->count(),
            'count4'      => \App\Models\Nasabah::where('kol', '4')->where('is_hb', 0)->count(),
            'count5'      => \App\Models\Nasabah::where('kol', '5')->where('is_hb', 0)->count(),
            'countHB'     => \App\Models\Nasabah::where('is_hb', 1)->count(),
        ];

        // Response untuk AJAX (Pagination / Filter Tab / Search)
        if ($request->ajax()) {
            return view('admin.partials.nasabah_table', $viewData)->render();
        }

        // 4. Response untuk Full Page Load (Refresh Halaman)
        $dashboard = new \App\Http\Controllers\Dashboard\DashboardAdminController();
        $data = $dashboard->getDashboardData();
        
        // PERBAIKAN DI SINI: Gabungkan data agar variabel $activeTab dkk tersedia di datakaryawan.blade.php
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
        // 1. Tambahkan nominal dan sisa_pokok ke dalam validasi
        $request->validate([
            'no_angsuran' => 'required|unique:nasabahs,no_angsuran',
            'nasabah'     => 'required',
            'alamat'      => 'required',
            'kol'         => 'required',
            'nominal'     => 'nullable|numeric',    // Boleh kosong, tapi jika isi harus angka
            'sisa_pokok'  => 'nullable|numeric',   // Boleh kosong, tapi jika isi harus angka
        ]);

        try {
            Nasabah::create([
                'no_angsuran'   => $request->no_angsuran,
                'nasabah'       => $request->nasabah,
                'alamat'        => $request->alamat,
                'kol'           => $request->kol,
                
                // 2. Ambil nilai dari request, jika kosong baru berikan default 0
                'nominal'       => $request->nominal ?? 0,
                'sisa_pokok'    => $request->sisa_pokok ?? 0,
                
                'kode_ao'       => '-',  
                'nama_ao'       => '-',  
                'kode'          => '-', 
                'sudah_kunjung' => 0,
                'bulan'         => now()->format('Y-m'),
            ]);

            return response()->json(['success' => 'Nasabah berhasil ditambahkan!']);
        } catch (\Exception $e) {
            // Menggunakan response json agar konsisten dengan AJAX di frontend
            return response()->json(['errors' => ['db' => [$e->getMessage()]]], 500);
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
