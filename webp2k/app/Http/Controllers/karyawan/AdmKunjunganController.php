<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use App\Models\DataKunjunganAdm;
use App\Models\Kunjungan;
use App\Models\Karyawan;
use App\Models\Nasabah;
use App\Models\User;
use App\Exports\KunjunganExport;
use App\Exports\JadwalKunjunganExport;
use App\Exports\DetailKunjunganExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\dashboard\DashboardAdminController;
use App\Notifications\UpdateStatusNotification;
use Carbon\Carbon;

class AdmKunjunganController extends Controller
{
   public function index(Request $request) 
    {
        // 1. Filter Dropdown (Tetap seperti aslinya)
        $nasabah_all = \App\Models\Nasabah::where('kode', 'LIKE', '%.8%')
            ->orderBy('nasabah', 'asc')->get();
        $karyawans = \App\Models\Karyawan::where('status', 'aktif')
            ->where('kode_ao', 'LIKE', 'C-%')->get();

        // 2. LOGIKA MERGING (PENTING)
        // Ambil Jadwal Admin
        $jadwal = \App\Models\DataKunjunganAdm::with('karyawan')
            ->where('bulan', now()->format('Y-m'))
            ->get();

        // Ambil Realisasi AO (Tabel kunjungans)
        $realisasi = \DB::table('kunjungans')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->get();

        $dataFinal = collect();
        $realisasiTerpakaiIds = [];

        // PROSES A: Masukkan data Jadwal Admin
        foreach ($jadwal as $j) {
            $match = $realisasi->first(function ($r) use ($j) {
                return strtoupper(trim($r->nama_nasabah)) === strtoupper(trim($j->nama_nasabah));
            });

            if ($match) { $realisasiTerpakaiIds[] = $match->id; }

            $dataFinal->push((object)[
                'id'             => $j->id,
                'kode_ao'        => $j->kode_ao,
                'nama_ao'        => $j->karyawan->nama ?? '-',
                'no_angsuran'    => $j->no_angsuran,
                // LOGIKA AGAR KODE MUNCUL:
                'kode_nasabah'   => $j->kode_nasabah ?? (\App\Models\Nasabah::where('no_angsuran', $j->no_angsuran)->value('kode') ?? '-'),
                'nama_nasabah'   => $j->nama_nasabah,
                'alamat_nasabah' => $j->alamat_nasabah,
                'kol'            => $j->kol ?? '-',
                'tanggal'        => $j->tanggal,
                'bulan'          => $j->bulan,
                'nominal'        => $j->nominal,
                'sisa_pokok'     => $j->sisa_pokok,
                'is_filled'      => $match ? true : false,
                'is_mandiri'     => false,
            ]);
        }

        // PROSES B: Masukkan data Realisasi Mandiri AO
        foreach ($realisasi as $r) {
            if (!in_array($r->id, $realisasiTerpakaiIds)) {
                $dataFinal->push((object)[
                    'id'             => $r->id,
                    'kode_ao'        => $r->kode_ao,
                    'nama_ao'        => '-',
                    'no_angsuran'    => $r->no_nasabah,
                    'kode_nasabah'   => \App\Models\Nasabah::where('no_angsuran', $r->no_nasabah)->value('kode') ?? '-',
                    'nama_nasabah'   => $r->nama_nasabah,
                    'alamat_nasabah' => $r->alamat_nasabah,
                    'kol'            => $r->kol ?? '-',
                    'tanggal'        => $r->created_at,
                    'bulan'          => \Carbon\Carbon::parse($r->created_at)->format('Y-m'),
                    'nominal'        => 0, // Atau ambil dari master jika perlu
                    'sisa_pokok'     => 0,
                    'is_filled'      => true,
                    'is_mandiri'     => true,
                ]);
            }
        }

        // 3. PAGINATION MANUAL (Wajib karena datanya Collection)
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $perPage = 15;
        $currentItems = $dataFinal->sortBy('kode_ao')->slice(($currentPage - 1) * $perPage, $perPage)->all();

        $kunjungans = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems, $dataFinal->count(), $perPage, $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        $kunjungansGrouped = $dataFinal->groupBy('kode_ao');

        // Query daftar_ao_jadwal (Tetap seperti aslinya)
        $daftar_ao_jadwal = \DB::table('data_kunjungan_adms')
            ->join('karyawans', 'data_kunjungan_adms.karyawan_id', '=', 'karyawans.id')
            ->select('karyawans.nama', 'data_kunjungan_adms.kode_ao', \DB::raw('count(*) as total_jadwal'))
            ->where('data_kunjungan_adms.bulan', 'LIKE', date('Y-m') . '%')
            ->groupBy('karyawans.nama', 'data_kunjungan_adms.kode_ao')
            ->orderBy('karyawans.nama', 'asc')->get();

        // 4. Return View
        if ($request->ajax()) {
            return view('admin.partials.input_kunjungan', [
                'kunjungans' => $kunjungans,
                'kunjungansGrouped' => $kunjungansGrouped,
                'nasabah_all' => $nasabah_all,
                'karyawans' => $karyawans
            ])->render();
        }

        $data['page'] = 'adm-kunjungan';
        $data['title'] = 'Input Jadwal Kunjungan';
        $data['content'] = view('admin.partials.input_kunjungan', compact('kunjungans', 'kunjungansGrouped', 'nasabah_all', 'karyawans','daftar_ao_jadwal'))->render();

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
       // 1. Ambil data dari ADM (Jadwal)
        $data_adm = \DB::table('data_kunjungan_adms')
            ->leftJoin('kunjungans', function ($join) {
                $join->on('data_kunjungan_adms.no_angsuran', '=', 'kunjungans.no_nasabah')
                     ->on('data_kunjungan_adms.kode_ao', '=', 'kunjungans.kode_ao')
                     // KUNCI DI SINI: Samakan tanggal jadwal dengan tanggal realisasi
                     ->whereRaw('DATE(kunjungans.created_at) = DATE(data_kunjungan_adms.created_at)');
            })
            ->leftJoin('nasabahs', 'data_kunjungan_adms.no_angsuran', '=', 'nasabahs.no_angsuran')
            ->where('data_kunjungan_adms.kode_ao', $kode_ao_clean)
            // Tambahkan filter bulan berjalan jika ingin benar-benar sama dengan HP Wahyu
            ->whereRaw('MONTH(data_kunjungan_adms.created_at) = ?', [date('m')])
            ->whereRaw('YEAR(data_kunjungan_adms.created_at) = ?', [date('Y')])
            ->select(
                'data_kunjungan_adms.no_angsuran',
                'data_kunjungan_adms.nama_nasabah',
                'data_kunjungan_adms.alamat_nasabah',
                'data_kunjungan_adms.created_at',
                'kunjungans.id as id_kunjungan',
                'kunjungans.status as status_kunjungan',
                'kunjungans.catatan as catatan_lapangan',
                'kunjungans.nominal_janji_bayar as nominal_janji_hasil',
                'kunjungans.tgl_janji_bayar as tgl_janji_hasil',
                'kunjungans.foto_kunjungan', 
                'kunjungans.created_at as tgl_realisasi',
                'nasabahs.nasabah as nama_nasabah_asli',
                'nasabahs.alamat as alamat_master'
            );

       // 2. Ambil data dari Kunjungan Mandiri
        $data_mandiri = \DB::table('kunjungans')
            ->leftJoin('data_kunjungan_adms', function ($join) {
                $join->on('kunjungans.no_nasabah', '=', 'data_kunjungan_adms.no_angsuran')
                     ->on('kunjungans.kode_ao', '=', 'data_kunjungan_adms.kode_ao')
                     // Sama seperti di atas
                     ->whereRaw('DATE(kunjungans.created_at) = DATE(data_kunjungan_adms.created_at)');
            })
            ->leftJoin('nasabahs', 'kunjungans.no_nasabah', '=', 'nasabahs.no_angsuran')
            ->where('kunjungans.kode_ao', $kode_ao_clean)
            ->whereNull('data_kunjungan_adms.no_angsuran')
            // KUNCI DI SINI: Batasi hanya bulan ini
            ->whereRaw('MONTH(kunjungans.created_at) = ?', [date('m')])
            ->whereRaw('YEAR(kunjungans.created_at) = ?', [date('Y')])
            ->select(
                'kunjungans.no_nasabah as no_angsuran',
                'kunjungans.nama_nasabah as nama_nasabah',
                'kunjungans.alamat_nasabah as alamat_nasabah',
                'kunjungans.created_at',
                'kunjungans.id as id_kunjungan',
                'kunjungans.status as status_kunjungan',
                'kunjungans.catatan as catatan_lapangan',
                'kunjungans.nominal_janji_bayar as nominal_janji_hasil',
                'kunjungans.tgl_janji_bayar as tgl_janji_hasil',
                'kunjungans.foto_kunjungan', 
                'kunjungans.created_at as tgl_realisasi',
                'nasabahs.nasabah as nama_nasabah_asli',
                'nasabahs.alamat as alamat_master'
            );

        $data_detail = $data_adm->union($data_mandiri)
            ->orderByRaw('CASE WHEN status_kunjungan IS NULL THEN 1 ELSE 0 END ASC')
            ->get();

        foreach ($data_detail as $item) {
            // 1. Logika Fallback Nama & Alamat (Tetap)
            $item->nama_nasabah = $item->nama_nasabah_asli ?? ($item->nama_nasabah ?? 'Nama Tidak Ada');
            $item->alamat_nasabah = $item->alamat_master ?? ($item->alamat_nasabah ?? 'Alamat Tidak Ada');

            // 2. Tambahan: Ambil data nasabah untuk keperluan Export per baris
            // Kita simpan ke dalam property item agar bisa dipanggil di Blade
            $item->info_nasabah = (object) [
                'no_angsuran' => $item->no_angsuran,
                'nama' => $item->nama_nasabah
            ];

            // Proses EXIF tetap sama
            if ($item->id_kunjungan && $item->foto_kunjungan) {
                $fotos = json_decode($item->foto_kunjungan, true);
                $namaFoto = (is_array($fotos) && count($fotos) > 0) ? $fotos[0] : $item->foto_kunjungan;
                $path = public_path('uploads/kunjungan/' . $namaFoto);
                if ($namaFoto && file_exists($path)) {
                    $exif = @exif_read_data($path);
                    if ($exif && isset($exif['GPSLatitude'])) {
                        $lat = $this->convertFractionToDecimal($exif['GPSLatitude'], $exif['GPSLatitudeRef'] ?? 'N');
                        $log = $this->convertFractionToDecimal($exif['GPSLongitude'], $exif['GPSLongitudeRef'] ?? 'E');
                        $item->koordinat = $lat . ',' . $log;
                    }
                }
            }
        }

        // 3. PENTING: Sediakan variabel $nasabah 'global' untuk Header agar Blade tidak error
        $nasabah = $data_detail->first() ?? (object) ['no_angsuran' => null, 'nasabah' => ''];
        $kode_ao = $kode_ao_clean;

        if (request()->ajax()) {
            return view('admin.partials.detail_kunjungan', compact('data_detail', 'kode_ao', 'nasabah'));
        }

        $karyawans = \DB::table('karyawans')->get(); 
        return view('admin.datakaryawan', compact('data_detail', 'kode_ao', 'karyawans', 'nasabah'));

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
        $nasabah = \DB::table('nasabahs')
            ->select(
                'no_angsuran', 
                'nasabah', 
                'alamat', 
                'kol', 
                'kode' // Ini yang isinya PG.001 sesuai Tinker
            )
            ->where(function($q) {
                $q->where('no_angsuran', 'LIKE', '150%')
                  ->orWhere('no_angsuran', 'LIKE', '8%');
            })
            ->orderBy('nasabah', 'asc')
            ->get();

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
            // --- TAMBAHKAN INI ---
            'kode_nasabah'   => $nasabahMaster->kode ?? '-', 
            // ---------------------
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

   public function hapusJadwalSingle($id)
    {
        try {
            // Gunakan DB agar lebih cepat atau Model jika ada
            $deleted = \DB::table('data_kunjungan_adms')->where('id', $id)->delete();

            if ($deleted) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Jadwal berhasil dihapus.'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan.'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
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

    public function exportJadwalExcel(Request $request)
    {
        $namaFile = 'jadwal_kunjungan_' . Carbon::now()->format('d-m-Y_H-i') . '.xlsx';

        try {
            return Excel::download(new JadwalKunjunganExport, $namaFile);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }

    public function exportDetailAO($kode_ao)
    {
        // Bersihkan kode AO jika ada embel-embel '-content'
        $kode_ao_clean = str_replace('-content', '', $kode_ao);
        
        $namaFile = 'Laporan_Kunjungan_AO_' . $kode_ao_clean . '_' . date('Y-m-d') . '.xlsx';

        // Kirim $kode_ao_clean ke Class Export
        return Excel::download(new DetailKunjunganExport($kode_ao_clean), $namaFile);
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
                // Kolom C = Index 2
                $noAngsuran = isset($row[2]) ? trim($row[2]) : null;

                if (empty($noAngsuran) || !is_numeric($noAngsuran)) continue;

                // Ambil data master nasabah untuk fallback data yang kosong
                $nasabahMaster = \App\Models\Nasabah::where('no_angsuran', (string)$noAngsuran)->first();

                $namaNasabah = $row[5] ?? ($nasabahMaster->nasabah ?? '-');
                $alamat      = $row[6] ?? ($nasabahMaster->alamat ?? '-');
                
                // PERBAIKAN: Gunakan Index 36 sesuai hitungan kamu
                $kolRaw = isset($row[36]) ? trim($row[36]) : 0;
                
                // Jika di excel 0, kosong, atau bukan angka, ambil dari database master nasabah
                if (empty($kolRaw) || $kolRaw == 0 || !is_numeric($kolRaw)) {
                    $kol = $nasabahMaster->kol ?? 1;
                } else {
                    $kol = (int)$kolRaw;
                }

                $nominal   = (float) preg_replace('/[^0-9]/', '', $row[10] ?? ($nasabahMaster->nominal ?? '0'));
                $sisaPokok = (float) preg_replace('/[^0-9]/', '', $row[11] ?? ($nasabahMaster->sisa_pokok ?? '0'));

                $isHb = ($kol == 5) ? true : false;

                \App\Models\DataKunjunganAdm::updateOrCreate(
                    [
                        'karyawan_id' => $request->karyawan_id,
                        'no_angsuran' => (string)$noAngsuran,
                        'bulan'       => now()->format('Y-m') 
                    ],
                    [
                        'kode_ao'        => $karyawan->kode_ao,
                        'nama_nasabah'   => $namaNasabah,
                        'alamat_nasabah' => $alamat,
                        'nominal'        => $nominal,
                        'sisa_pokok'     => $sisaPokok,
                        'kol'            => $kol,
                        'is_hb'          => $isHb,
                        'tanggal'        => $tglInput, 
                    ]
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Import Berhasil dengan KOL dari kolom 36!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            // 1. Update status kunjungan
            DB::table('kunjungans')->where('id', $id)->update([
                'status' => $request->status,
                'updated_at' => now()
            ]);

            // 2. Ambil data kunjungan untuk cari AO
            $dataKunjungan = DB::table('kunjungans')->where('id', $id)->first();

            if ($dataKunjungan) {
                // Bersihkan kode_ao dari titik/spasi untuk pencarian cadangan
                $cleanAo = preg_replace('/[^A-Za-z0-9]/', '', $dataKunjungan->kode_ao);

                // Cari di tabel karyawans
                $ao = \App\Models\Karyawan::where('kode_ao', $dataKunjungan->kode_ao)
                    ->orWhereRaw("REPLACE(REPLACE(kode_ao, '.', ''), ' ', '') = ?", [$cleanAo])
                    ->first();

                if ($ao) {
                    // KIRIM NOTIFIKASI
                    $ao->notify(new \App\Notifications\UpdateStatusNotification([
                        'nama_nasabah' => $dataKunjungan->nama_nasabah,
                        'status' => $request->status,
                        'id_kunjungan' => $id
                    ]));
                } else {
                    // Log jika AO tidak ketemu (cek di storage/logs/laravel.log)
                    \Log::warning("AO tidak ditemukan untuk kode: " . $dataKunjungan->kode_ao);
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function operJadwal(Request $request, $id) {
        $jadwal = DataKunjunganAdm::findOrFail($id);
        $jadwal->kode_ao = $request->kode_ao_baru; // Misal dioper ke C-005
        $jadwal->save();

        return back()->with('success', 'Jadwal berhasil dioper ke AO lain.');
    }

    public function resetJadwal()
    {
        try {
            // Menggunakan truncate untuk mengosongkan tabel dan mereset ID auto_increment
            \DB::table('data_kunjungan_adms')->truncate();

            return response()->json([
                'message' => 'Jadwal berhasil dikosongkan. Silakan buat jadwal baru.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteSelected(Request $request)
    {
        $kode_ao_list = $request->ids;

        if (!empty($kode_ao_list)) {
            \DB::table('data_kunjungan_adms')
                ->whereIn('kode_ao', $kode_ao_list)
                ->where('bulan', 'LIKE', date('Y-m') . '%')
                ->delete();

            \DB::table('kunjungans')
                ->whereIn('kode_ao', $kode_ao_list)
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->delete();

            return response()->json([
                'success' => 'Jadwal dan History kunjungan AO berhasil dihapus. Rekap sudah sinkron.'
            ]);
        }
        
        return response()->json(['error' => 'Gagal menghapus data, tidak ada AO terpilih.'], 400);
    }

    public function getDaftarAOHapus()
    {
        $data = \DB::table('data_kunjungan_adms')
            ->join('karyawans', 'data_kunjungan_adms.kode_ao', '=', 'karyawans.kode_ao')
            ->select(
                'data_kunjungan_adms.kode_ao', 
                'karyawans.nama', 
                \DB::raw('count(*) as total_jadwal')
            )
            ->where('data_kunjungan_adms.bulan', 'LIKE', date('Y-m') . '%')
            ->groupBy('data_kunjungan_adms.kode_ao', 'karyawans.nama')
            ->get();

        return response()->json($data);
    }
}