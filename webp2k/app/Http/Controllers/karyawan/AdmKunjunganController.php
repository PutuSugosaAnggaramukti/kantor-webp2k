<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use App\Models\DataKunjunganAdm;
use App\Models\Kunjungan;
use App\Models\Karyawan;
use App\Exports\KunjunganExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Dashboard\DashboardAdminController;

class AdmKunjunganController extends Controller
{
    public function index(Request $request) 
    {
        $karyawans = Karyawan::where('status', 'aktif')->get();
        
        $kunjungansGrouped = \App\Models\DataKunjunganAdm::with('karyawan')
        ->where('bulan', now()->format('Y-m')) 
        ->get()
        ->groupBy('kode_ao');

        $kunjungans = DataKunjunganAdm::with('karyawan')
            ->orderBy('kode_ao', 'asc')
            ->orderBy('kol', 'desc')
            ->paginate(15); 

        if ($request->ajax()) {
            return view('admin.partials.input_kunjungan', compact('karyawans', 'kunjungans', 'kunjungansGrouped'))->render();
        }

        try {
            $dashboard = new DashboardAdminController();
            $data = $dashboard->getDashboardData();
        } catch (\Exception $e) {
            $data = ['karyawan_count' => Karyawan::count()];
        }

        $data['page'] = 'adm-kunjungan';
        $data['title'] = 'Input Jadwal Kunjungan';
        
        // Tambahkan kunjungansGrouped di compact sini juga
        $data['content'] = view('admin.partials.input_kunjungan', compact('karyawans', 'kunjungans', 'kunjungansGrouped'))->render();
        $data['karyawans'] = $karyawans; 

        return view('admin.datakaryawan', $data);
    }
    
  public function dataKunjunganContent(Request $request)
    {
        $karyawans = Karyawan::where('status', 'aktif')
            ->withCount('kunjungan') 
            ->get();

        // Samakan nama variabelnya dengan yang di index
        $kunjungansGrouped = \App\Models\Nasabah::with('karyawan')
            ->orderByRaw("kol = 5 DESC")
            ->get()
            ->groupBy('kode_ao');

        if ($request->ajax()) {
            return view('admin.partials.kunjungan', compact('karyawans', 'kunjungansGrouped'))->render();
        }

        try {
            $dashboard = new DashboardAdminController();
            $data = $dashboard->getDashboardData(); 
        } catch (\Exception $e) {
            $data = ['karyawan_count' => Karyawan::count()];
        }

        $data['title'] = 'Data Kunjungan';
        $data['page'] = 'kunjungan';
        $data['content'] = view('admin.partials.kunjungan', compact('karyawans', 'kunjungansGrouped'))->render();
        $data['karyawans'] = $karyawans;

        return view('admin.datakaryawan', $data);
    }

    public function detail($kode_ao)
    {
        try {
            $data_detail = \DB::table('kunjungans')
                ->leftJoin('nasabahs', 'kunjungans.no_nasabah', '=', 'nasabahs.no_angsuran')
                ->leftJoin('data_kunjungan_adms', function ($join) {
                    $join->on('kunjungans.nama_nasabah', '=', 'data_kunjungan_adms.nama_nasabah')
                        ->on('kunjungans.kode_ao', '=', 'data_kunjungan_adms.kode_ao');
                })
                ->where('kunjungans.kode_ao', $kode_ao)
                ->select(
                    'kunjungans.id',
                    'kunjungans.created_at',
                    'kunjungans.nama_nasabah',
                    'kunjungans.no_nasabah',
                    'nasabahs.alamat as alamat_master',
                    'data_kunjungan_adms.alamat_nasabah as alamat_rencana',
                    'data_kunjungan_adms.no_angsuran as no_rencana'
                )
                ->orderBy('kunjungans.created_at', 'desc')
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

        // 1. Cari data nasabah di tabel master untuk ambil nominal & sisa pokok
        $nasabahMaster = \App\Models\Nasabah::where('no_angsuran', $request->no_angsuran)->first();
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
            
            // SINKRONISASI SALDO OTOMATIS
            'nominal'        => $nasabahMaster->nominal ?? 0,
            'sisa_pokok'     => $nasabahMaster->sisa_pokok ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal kunjungan berhasil ditambahkan!'
        ]);
    }

    public function rekapKunjungan()
    {
        $rekap = Karyawan::withCount(['kunjungan as jumlah_kunjungan'])->get();
        return view('admin.rekap_kunjungan_content', compact('rekap'));
    }

    public function exportExcel(Request $request)
    {
        $kode_ao = $request->query('kode_ao');
        return Excel::download(new KunjunganExport($kode_ao), 'rekap_kunjungan_' . date('Y-m-d') . '.xlsx');
    }

  public function importExcel(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'file_excel'  => 'required|mimes:xlsx,xls'
        ]);

        try {
            $file = $request->file('file_excel');
            $data = Excel::toArray([], $file)[0];
            $karyawan = Karyawan::find($request->karyawan_id);

            DB::beginTransaction();

            // Skip header (Baris 1)
            foreach (array_slice($data, 1) as $row) {
                $noAng = $row[0] ?? null;
                if (empty($noAng)) continue;

                // 1. Simpan ke tabel Master Nasabah
                \App\Models\Nasabah::updateOrCreate(
                    ['no_angsuran' => (string)$noAng],
                    [
                        'nasabah'       => $row[1] ?? '-', 
                        'alamat'        => $row[2] ?? '-', 
                        'nominal'       => $row[3] ?? 0,   
                        'sisa_pokok'    => $row[4] ?? 0,   
                        'kol'           => $row[5] ?? 1,   
                        'kode_ao'       => $karyawan->kode_ao, 
                        'bulan'         => now()->format('Y-m'),
                        'sudah_kunjung' => 0
                    ]
                );

                // 2. Simpan ke tabel Jadwal Kunjungan agar langsung muncul di Accordion
                \App\Models\DataKunjunganAdm::updateOrCreate(
                    [
                        'no_angsuran' => (string)$noAng,
                        'bulan'       => now()->format('Y-m')
                    ],
                    [
                        'karyawan_id'    => $request->karyawan_id,
                        'kode_ao'        => $karyawan->kode_ao,
                        'nama_nasabah'   => $row[1] ?? '-',
                        'alamat_nasabah' => $row[2] ?? '-',
                        'nominal'        => $row[3] ?? 0,
                        'sisa_pokok'     => $row[4] ?? 0,
                        'kol'            => $row[5] ?? 1,
                        'bulan'          => now()->format('Y-m'),
                        'tanggal'        => now(),
                    ]
                );
            }

            DB::commit();
            return response()->json([
                'success' => true, 
                'message' => 'Data berhasil diimport untuk AO: ' . $karyawan->nama
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    }
}