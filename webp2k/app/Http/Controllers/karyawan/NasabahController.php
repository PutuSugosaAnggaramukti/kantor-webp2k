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
        $nasabah_all = \App\Models\Nasabah::orderBy('nasabah', 'asc')->paginate(10)->withQueryString();

        // Jika lewat AJAX (klik pagination)
        if ($request->ajax()) {
            // Render HANYA isi tabel dan link paginationnya saja
            return view('admin.partials.nasabah_table', compact('nasabah_all'))->render();
        }

        // Jika akses pertama kali (bukan AJAX)
        return view('admin.partials.nasabah_table', compact('nasabah_all'));
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
        return response()->json(Nasabah::select('no_angsuran', 'nasabah')->get());
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
            // Jika masih ada error field lain, ini akan menampilkannya
            return "Terjadi Error Database: " . $e->getMessage(); 
        }
    }
}
