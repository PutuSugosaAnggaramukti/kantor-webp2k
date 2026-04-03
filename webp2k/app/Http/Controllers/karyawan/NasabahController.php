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
        // 1. Ambil parameter tab (default: semua)
        $activeTab = $request->query('tab', 'semua');

        // 2. Query Dasar
        $query = \App\Models\Nasabah::orderBy('nasabah', 'asc');

        // 3. Logika Filter Tab
        if ($activeTab == 'hb') {
            // Hanya ambil KOL 5
            $query->where('kol', '5');
        } else {
            // Ambil KOL 1-4 (Reguler)
            $query->whereIn('kol', [1, 2, 3, 4]);
        }

        // 4. Eksekusi Pagination
        $nasabah_all = $query->paginate(10)->withQueryString();

        // 5. Data Pendukung (Badge Notifikasi) - Tetap dihitung agar angka di tab akurat
        $countReguler = \App\Models\Nasabah::whereIn('kol', [1, 2, 3, 4])->count();
        $countHB = \App\Models\Nasabah::where('kol', '5')->count();

        // Jika request AJAX (Dari switchTab atau Pagination)
        if ($request->ajax()) {
            return view('admin.partials.nasabah_table', [
                'nasabah_all' => $nasabah_all,
                'countReguler' => $countReguler,
                'countHB' => $countHB,
                'activeTab' => $activeTab // Kirim status tab aktif ke view
            ])->render();
        }

        // Data untuk load halaman pertama kali
        $dashboard = new \App\Http\Controllers\Dashboard\DashboardAdminController();
        $data = $dashboard->getDashboardData();

        $data['content'] = view('admin.partials.nasabah_table', [
            'nasabah_all' => $nasabah_all,
            'countReguler' => $countReguler,
            'countHB' => $countHB,
            'activeTab' => $activeTab
        ])->render();
        
        $data['page'] = 'nasabah';
        $data['title'] = 'Data Nasabah';

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
            \DB::table('nasabahs')->truncate();
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\NasabahImport, $request->file('file'));
            
           return redirect()->back()->with('success', 'Data Nasabah Berhasil Diperbarui!');
        } catch (\Exception $e) {
            return "Terjadi Error Database: " . $e->getMessage(); 
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
