<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use App\Exports\NasabahExport;
use App\Imports\NasabahImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Nasabah;
use App\Models\DataKunjunganAdm;
use Illuminate\Http\Request;

class NasabahController extends Controller
{

    public function nasabahContent(Request $request)
    {
        // 1. Ambil data nasabah
        $nasabah_all = \App\Models\Nasabah::orderBy('nasabah', 'asc')
                        ->paginate(10)
                        ->withQueryString();

        // --- LOGIKA HYBRID START ---
        
        // A. Jika request via AJAX (Klik menu sidebar / Pagination)
        if ($request->ajax()) {
            // Hanya kirim tabelnya saja
            return view('admin.partials.nasabah_table', compact('nasabah_all'))->render();
        }

        // B. Jika request via Refresh / Direct URL (F5)
        // Panggil dashboard controller untuk mengambil data Sidebar/Stats
        $dashboard = new \App\Http\Controllers\Dashboard\DashboardAdminController();
        $data = $dashboard->getDashboardData();

        // Masukkan isi tabel nasabah ke variabel 'content'
        $data['content'] = view('admin.partials.nasabah_table', compact('nasabah_all'))->render();
        $data['page'] = 'nasabah';
        $data['title'] = 'Data Nasabah';

        // Kembalikan ke view UTAMA (yang ada sidebar-nya)
        return view('admin.datakaryawan', $data);

        // --- LOGIKA HYBRID END ---
    }

    public function detail($no_angsuran)
    {
        $histori_kunjungan = DataKunjunganAdm::where('no_angsuran', $no_angsuran)
            ->with('karyawan')
            ->get();

        // Pastikan view-nya mengarah ke file yang sedang kita edit
        return view('admin.partials.pengunjung_nasabah', compact('histori_kunjungan'));
    }

   public function store(Request $request)
    {
        $request->validate([
            'no_angsuran' => 'required|unique:nasabahs,no_angsuran',
            'nasabah'     => 'required',
            'alamat'      => 'required',
            'kol'         => 'required'
        ]);

        try {
            Nasabah::create([
                'no_angsuran'   => $request->no_angsuran,
                'nasabah'       => $request->nasabah,
                'alamat'        => $request->alamat,
                'kol'           => $request->kol,
                
                'kode_ao'       => '-',  
                'nama_ao'       => '-',  
                'kode'          => '-', 
                'nominal'       => 0,
                'sisa_pokok'    => 0,
                'sudah_kunjung' => 0,
                'bulan'         => now()->format('Y-m'),
            ]);

            return response()->json(['success' => 'Nasabah berhasil ditambahkan!']);
        } catch (\Exception $e) {
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
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\NasabahImport, $request->file('file'));
            
            return redirect()->route('admin.dashboard', ['page' => 'nasabah'])
                            ->with('success', 'Data Nasabah berhasil diimport!');
        } catch (\Exception $e) {
            return "Terjadi Error Database: " . $e->getMessage(); 
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
