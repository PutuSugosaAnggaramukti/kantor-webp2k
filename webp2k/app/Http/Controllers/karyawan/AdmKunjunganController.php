<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use App\Models\DataKunjunganAdm; // Model Jadwal Kunjungan (Input Admin)
use App\Models\Kunjungan;        // Model Hasil Kunjungan (Input AO di Lapangan)
use App\Models\Karyawan;
use App\Exports\KunjunganExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdmKunjunganController extends Controller
{
   public function index()
    {
        $karyawans = Karyawan::all();
        $kunjungansGrouped = DataKunjunganAdm::with('karyawan')
            ->orderBy('kol', 'desc')
            ->get()
            ->groupBy('kode_ao'); 

        return view('admin.partials.input_kunjungan', compact('karyawans', 'kunjungansGrouped'));
    }
    
   public function dataKunjunganContent()
    {
        // 1. Ambil data karyawan beserta hitungan kunjungannya
        // Kita namakan $karyawan agar sesuai dengan @foreach di Blade kamu
        $karyawan = \App\Models\Karyawan::where('status', 'aktif')
            ->get()
            ->map(function ($item) {
                // Hitung jumlah kunjungan selesai dari tabel kunjungans
                $item->kunjungan_count = \DB::table('kunjungans')
                    ->where('kode_ao', $item->kode_ao)
                    ->count();
                return $item;
            });

        // 2. Jika kamu masih butuh data detail kunjungan yang di-join ke tabel nasabahs
        $kunjunganGrouped = \DB::table('kunjungans')
            ->leftJoin('nasabahs', 'kunjungans.no_nasabah', '=', 'nasabahs.no_angsuran') 
            ->select('kunjungans.*', 'nasabahs.kol')
            ->orderBy('kunjungans.created_at', 'desc')
            ->get()
            ->groupBy('kode_ao');

        // Kirim kedua variabel tersebut ke view
        return view('admin.partials.kunjungan', compact('karyawan', 'kunjunganGrouped'));
    }

    public function detail($kode_ao)
    {
        try {
            $data_detail = DataKunjunganAdm::whereHas('karyawan', function($q) use ($kode_ao) {
                    $q->where('kode_ao', $kode_ao);
                })
                ->orderBy('tanggal', 'desc')
                ->get();

            return view('admin.partials.detail_kunjungan', compact('data_detail', 'kode_ao'));
            
        } catch (\Exception $e) {
            return "<div style='color:red; padding:20px;'>Error: " . $e->getMessage() . "</div>";
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'karyawan_id'    => 'required|exists:karyawans,id',
            'nama_nasabah'   => 'required|string|max:255',
            'alamat_nasabah' => 'required',
            'kol'            => 'required',
            'bulan'          => 'required',
            'no_angsuran'    => 'required',
            'tanggal'        => 'required|date',
        ]);

        $karyawan = Karyawan::find($request->karyawan_id);

        DataKunjunganAdm::create([
            'karyawan_id'    => $request->karyawan_id,
            'nama_nasabah'   => $request->nama_nasabah,
            'alamat_nasabah' => $request->alamat_nasabah,
            'kol'            => $request->kol,
            'bulan'          => $request->bulan,
            'no_angsuran'    => $request->no_angsuran,
            'tanggal'        => $request->tanggal, 
            'kode_ao'        => $karyawan->kode_ao ?? null,
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Jadwal kunjungan berhasil ditambahkan!'
        ]);
    }

    public function rekapKunjungan()
    {
        // Tetap menggunakan count untuk summary global jika diperlukan
        $rekap = Karyawan::withCount(['kunjungan as jumlah_kunjungan'])->get();
        return view('admin.rekap_kunjungan_content', compact('rekap'));
    }

    public function exportExcel()
    {
        return Excel::download(new KunjunganExport, 'rekap_seluruh_kunjungan.xlsx');
    }
}