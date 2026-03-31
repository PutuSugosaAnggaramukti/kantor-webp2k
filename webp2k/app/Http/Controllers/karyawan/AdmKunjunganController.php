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
        $nasabah_all = \App\Models\Nasabah::orderBy('nasabah', 'asc')->get();
        $karyawans = \App\Models\Karyawan::where('status', 'aktif')->get();
        // $nasabah_cek = \App\Models\Nasabah::where('no_angsuran', '26000001')->first();

        // dd($nasabah_cek);

        $kunjungansGrouped = \App\Models\DataKunjunganAdm::with('karyawan')
            ->where('bulan', now()->format('Y-m')) 
            ->get()
            ->groupBy('kode_ao');

        $kunjungans = \App\Models\DataKunjunganAdm::with('karyawan')
            ->orderBy('kode_ao', 'asc')
            ->paginate(15); 

        // Jika request AJAX (saat panggil loadAdminPage)
        if ($request->ajax()) {
            return view('admin.partials.input_kunjungan', [
                'kunjungans' => $kunjungans,
                'kunjungansGrouped' => $kunjungansGrouped,
                'nasabah_all' => \App\Models\Nasabah::orderBy('nasabah', 'asc')->get(), // Ambil fresh
                'karyawans' => \App\Models\Karyawan::where('status', 'aktif')->get() // WAJIB ADA
            ])->render();
        }

        $data['page'] = 'adm-kunjungan';
        $data['title'] = 'Input Jadwal Kunjungan';
        // Kirim juga ke konten utama
        $data['content'] = view('admin.partials.input_kunjungan', compact('kunjungans', 'kunjungansGrouped', 'nasabah_all', 'karyawans'))->render();

        return view('admin.datakaryawan', $data);
    }
    
 public function dataKunjunganContent(Request $request)
    {
        $karyawans = Karyawan::where('status', 'aktif')->withCount('kunjungan')->get();
        $nasabah_all = \App\Models\Nasabah::orderBy('nasabah', 'asc')->get();

        $kunjungansGrouped = \App\Models\DataKunjunganAdm::with('karyawan')
            ->where('bulan', now()->format('Y-m'))
            ->get()
            ->groupBy('kode_ao');

        if ($request->ajax()) {
            return view('admin.partials.kunjungan', compact('karyawans', 'kunjungansGrouped', 'nasabah_all'))->render();
        }

        // Bagian Dashboard
        try {
            $dashboard = new DashboardAdminController();
            $data = $dashboard->getDashboardData(); 
        } catch (\Exception $e) {
            $data = ['karyawan_count' => Karyawan::count()];
        }

        $data['title'] = 'Data Kunjungan';
        $data['page'] = 'kunjungan';
        $data['content'] = view('admin.partials.kunjungan', compact('karyawans', 'kunjungansGrouped', 'nasabah_all'))->render();
        $data['karyawans'] = $karyawans;
        $data['nasabah_all'] = $nasabah_all;

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
                    'kunjungans.*', // Ambil semua kolom agar aman
                    'nasabahs.alamat as alamat_master',
                    'data_kunjungan_adms.alamat_nasabah as alamat_rencana'
                )
                ->orderBy('kunjungans.created_at', 'desc')
                ->get();

            // LOGIKA BARU: Update koordinat dari EXIF foto jika tersedia
            foreach ($data_detail as $item) {
                $path = public_path('uploads/kunjungan/' . $item->foto_kunjungan);
                
                if ($item->foto_kunjungan && file_exists($path)) {
                    $exif = @exif_read_data($path);
                    if (isset($exif['GPSLatitude']) && isset($exif['GPSLongitude'])) {
                        // Jika foto punya koordinat asli, timpa data koordinat database
                        $item->koordinat = $this->getGps($exif);
                    }
                }
            }

            return view('admin.partials.detail_kunjungan', compact('data_detail', 'kode_ao'));
        } catch (\Exception $e) {
            return "<div style='color:red; padding:20px;'>Error: " . $e->getMessage() . "</div>";
        }
    }

    // Tambahkan helper function ini di bawah method detail atau di bawah class
    private function getGps($exif)
    {
        $lat = $this->getComponent($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
        $lon = $this->getComponent($exif['GPSLongitude'], $exif['GPSLongitudeRef']);
        return "$lat, $lon";
    }

    private function getComponent($coordinate, $ref)
    {
        $degrees = count($coordinate) > 0 ? $this->solveFraction($coordinate[0]) : 0;
        $minutes = count($coordinate) > 1 ? $this->solveFraction($coordinate[1]) : 0;
        $seconds = count($coordinate) > 2 ? $this->solveFraction($coordinate[2]) : 0;
        $flip = ($ref == 'W' || $ref == 'S') ? -1 : 1;
        return $flip * ($degrees + $minutes / 60 + $seconds / 3600);
    }

    private function solveFraction($fraction)
    {
        $parts = explode('/', $fraction);
        if (count($parts) <= 0) return 0;
        if (count($parts) == 1) return $parts[0];
        return $parts[1] == 0 ? 0 : $parts[0] / $parts[1];
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

        $cekDuplikat = DataKunjunganAdm::where('karyawan_id', $request->karyawan_id)
                        ->where('no_angsuran', $request->no_angsuran)
                        ->where('bulan', $request->bulan)
                        ->exists();

        if ($cekDuplikat) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal untuk nasabah ini sudah dibuat oleh AO tersebut di bulan ini!'
            ], 422); 
        }

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
            'file_excel'  => 'required|mimes:xlsx,xls',
            'tanggal_kunjungan' => 'required|date'
        ]);

        try {
            $file = $request->file('file_excel');
            $data = Excel::toArray([], $file)[0];
            $karyawan = Karyawan::find($request->karyawan_id);
            
            $tglInput = $request->tanggal_kunjungan;

            DB::beginTransaction();

            foreach (array_slice($data, 1) as $row) {
                $noAngsuran  = $row[0] ?? null; 
                $namaNasabah = $row[1] ?? null; 
                $alamat      = $row[2] ?? '-';  
                $nominal     = $row[3] ?? 0;    
                $sisaPokok   = $row[4] ?? 0;    
                $kol         = $row[5] ?? 1;    

                if (empty($namaNasabah)) continue;

                // --- LOGIKA OTOMATIS HB ---
                // Jika KOL di excel adalah 5, maka is_hb diset true (1)
                $isHb = ($kol == 5) ? true : false;

                \App\Models\DataKunjunganAdm::updateOrCreate(
                    [
                        'karyawan_id'  => $request->karyawan_id,
                        'no_angsuran'  => $noAngsuran,
                        'bulan'        => now()->format('Y-m') 
                    ],
                    [
                        'kode_ao'        => $karyawan->kode_ao,
                        'nama_nasabah'   => $namaNasabah,
                        'alamat_nasabah' => $alamat,
                        'nominal'        => (float) $nominal,
                        'sisa_pokok'     => (float) $sisaPokok,
                        'kol'            => $kol,
                        'is_hb'          => $isHb,      // Masukkan status HB ke database
                        'tanggal'        => $tglInput, 
                    ]
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Import Berhasil untuk tanggal ' . $tglInput . '!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}