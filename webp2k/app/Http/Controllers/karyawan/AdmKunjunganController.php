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
    // 1. Ambil keyword dari request search
    $keyword = $request->search;

    // Tambahkan filter 'when' agar database hanya menarik data yang sesuai keyword
    $karyawans = Karyawan::where('status', 'aktif')
        ->when($keyword, function($query) use ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('nama', 'like', "%$keyword%")
                  ->orWhere('kode_ao', 'like', "%$keyword%");
            });
        })
        ->get();

    // 2. Kita hitung manual angka-angkanya (Logika Sam tetap dipertahankan)
    $karyawans->map(function($karyawan) {
        $karyawan->kunjungan_count = \DB::table('data_kunjungan_adms')
            ->where('kode_ao', $karyawan->kode_ao)
            ->where('bulan', now()->format('Y-m'))
            ->count();

        $karyawan->total_realisasi = \DB::table('kunjungans')
            ->where('kode_ao', $karyawan->kode_ao)
            ->whereNotNull('catatan')
            ->where('catatan', '!=', '')
            ->count();
        
        return $karyawan;
    });

    $nasabah_all = \App\Models\Nasabah::orderBy('nasabah', 'asc')->get();

    $kunjungansGrouped = \App\Models\DataKunjunganAdm::with('karyawan')
        ->where('bulan', now()->format('Y-m'))
        ->get()
        ->groupBy('kode_ao');

    // Cek jika request datang dari AJAX (saat user mengetik di searchInput)
    if ($request->ajax()) {
        return view('admin.partials.kunjungan', compact('karyawans', 'kunjungansGrouped', 'nasabah_all'))->render();
    }

    // Bagian Dashboard (untuk load halaman pertama kali)
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
    $kode_ao_clean = str_replace('-content', '', $kode_ao);

    try {
        // QUERY DIBALIK: Mulai dari data_kunjungan_adms (Tabel Rencana)
        $data_detail = \DB::table('data_kunjungan_adms')
            // Hubungkan ke hasil kunjungan (kunjungans) jika sudah ada
            ->leftJoin('kunjungans', function ($join) {
                $join->on('data_kunjungan_adms.no_angsuran', '=', 'kunjungans.no_nasabah')
                     ->on('data_kunjungan_adms.kode_ao', '=', 'kunjungans.kode_ao');
            })
            // Hubungkan ke master nasabah untuk alamat jika perlu
            ->leftJoin('nasabahs', 'data_kunjungan_adms.no_angsuran', '=', 'nasabahs.no_angsuran')
            ->where('data_kunjungan_adms.kode_ao', $kode_ao_clean)
            ->select(
                    'data_kunjungan_adms.*', 
                    'kunjungans.id as id_kunjungan',
                    'kunjungans.status as status_kunjungan',
                    'kunjungans.catatan as catatan_lapangan',
                    'kunjungans.tgl_janji_bayar as tgl_janji_hasil',
                    'kunjungans.foto_kunjungan', 
                    'kunjungans.nominal_janji_bayar as nominal_janji_hasil',
                    'kunjungans.created_at as tgl_realisasi',
                    'nasabahs.alamat as alamat_master'
                )
            ->orderBy('data_kunjungan_adms.created_at', 'desc')
            ->get();

        // Logika pengolahan foto (Hanya dilakukan jika id_kunjungan tidak null)
        foreach ($data_detail as $item) {
            if ($item->id_kunjungan) {
                $fotos = json_decode($item->foto_kunjungan, true);
                $namaFoto = (is_array($fotos) && count($fotos) > 0) ? $fotos[0] : $item->foto_kunjungan;
                
                $path = public_path('uploads/kunjungan/' . $namaFoto);
                
                if ($namaFoto && file_exists($path)) {
                    $exif = @exif_read_data($path);
                    if ($exif && isset($exif['GPSLatitude']) && isset($exif['GPSLongitude'])) {
                        $lat = $this->convertFractionToDecimal($exif['GPSLatitude'], $exif['GPSLatitudeRef'] ?? 'N');
                        $log = $this->convertFractionToDecimal($exif['GPSLongitude'], $exif['GPSLongitudeRef'] ?? 'E');
                        $item->koordinat = $lat . ',' . $log;
                    }
                }
            } else {
                // Jika belum dikunjungi, set koordinat kosong
                $item->koordinat = null;
            }
        }

        $kode_ao = $kode_ao_clean;

        if (request()->ajax()) {
            return view('admin.partials.detail_kunjungan', compact('data_detail', 'kode_ao'));
        }

        return view('admin.kunjungan_detail_full', compact('data_detail', 'kode_ao'));

    } catch (\Exception $e) {
        \Log::error("Error Detail Kunjungan: " . $e->getMessage());
        return request()->ajax() 
            ? "<div class='alert alert-danger'>Gagal: " . $e->getMessage() . "</div>" 
            : redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
    }
}
        // Tambahkan helper function ini di bawah method detail atau di bawah class
        private function convertFractionToDecimal($exifCoord, $hemi)
    {
        // Cek jika data koordinat valid
        if (!is_array($exifCoord) || count($exifCoord) < 3) return 0;

        $degrees = $this->evalFraction($exifCoord[0]);
        $minutes = $this->evalFraction($exifCoord[1]);
        $seconds = $this->evalFraction($exifCoord[2]);

        $flip = ($hemi == 'S' || $hemi == 'W') ? -1 : 1;

        return $flip * ($degrees + ($minutes / 60) + ($seconds / 3600));
    }

    private function evalFraction($fraction)
    {
        // Jika formatnya "7/1" atau "3600/100"
        if (is_string($fraction) && strpos($fraction, '/') !== false) {
            $parts = explode('/', $fraction);
            if (count($parts) == 2 && $parts[1] != 0) {
                return (float) $parts[0] / (float) $parts[1];
            }
        }
        return (float) $fraction;
    }

    public function getDaftarNoAnggota()
    {
        try {
            // Mengambil seluruh data tanpa terkecuali
            $nasabah = \App\Models\Nasabah::select('no_angsuran', 'nasabah', 'alamat', 'kol')
                ->orderByRaw("CASE WHEN kol = '5' THEN 0 ELSE 1 END") 
                ->orderBy('nasabah', 'asc')
                ->get();

            // Log jumlah data yang dikirim untuk dicek di storage/logs/laravel.log
            // \Log::info('Jumlah nasabah dikirim ke dropdown: ' . $nasabah->count());

            return response()->json($nasabah);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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

        $isHb = ($request->kol == 5) ? true : false;

        DataKunjunganAdm::create([
            'karyawan_id'    => $request->karyawan_id,
            'nama_nasabah'   => $request->nama_nasabah,
            'alamat_nasabah' => $request->alamat_nasabah,
            'kol'            => $request->kol,
            'is_hb'          => $isHb, 
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
        
        $fileName = 'Rekap_Kunjungan_' . ($kode_ao ?: 'SEMUA') . '_' . date('Y-m-d') . '.xlsx';

        return Excel::download(new KunjunganExport($kode_ao), $fileName);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'file_excel'  => 'required|mimes:xlsx,xls,csv',
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
                
                $nominalRaw   = preg_replace('/[^0-9]/', '', $row[3] ?? '0');
                $sisaPokokRaw = preg_replace('/[^0-9]/', '', $row[4] ?? '0');
                
                $kol = $row[5] ?? 1;    

                if (empty($namaNasabah)) continue;

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
                        'nominal'        => (float) $nominalRaw,   
                        'sisa_pokok'     => (float) $sisaPokokRaw, 
                        'kol'            => $kol,
                        'is_hb'          => $isHb,
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

        public function updateStatus(Request $request, $id)
    {
        try {
            \DB::table('kunjungans')->where('id', $id)->update([
                'status' => $request->status,
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diperbarui!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ], 500);
        }
    }
}