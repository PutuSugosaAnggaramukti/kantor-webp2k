<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Models\DataKunjunganAdm;
use App\Models\HasilKunjungan;
use App\Models\Nasabah;
use App\Helpers\ExifHelper;
use App\Models\Karyawan;
use App\Models\Kunjungan;
use App\Exports\KunjunganExport;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use Maatwebsite\Excel\Facades\Excel;

class KunjunganController extends Controller
{

    public function index()
    {
        return $this->getMergedKunjunganData();
    }

    public function boot()
    {
        Paginator::useBootstrapFive(); 
    }

   public function dataKunjunganContent()
    {
        return $this->getMergedKunjunganData();
    }

    private function getMergedKunjunganData()
    {
        $karyawan = Auth::guard('karyawan')->user();
        if (!$karyawan) return redirect()->back();

        $myCode = strtoupper(trim($karyawan->kode_ao));
        
        // 1. Ambil data
        $jadwal = DataKunjunganAdm::where('karyawan_id', $karyawan->id)->get();
        $realisasi = \DB::table('kunjungans')
            ->where('kode_ao', 'LIKE', '%' . $myCode . '%')
            ->get();

        $dataFinal = collect();
        $realisasiTerpakaiIds = []; // Simpan ID realisasi yang sudah masuk di PROSES A

        // PROSES A: Loop Jadwal Admin
        foreach ($jadwal as $j) {
            $namaJadwal = strtoupper(trim(preg_replace('/\s+/', ' ', $j->nama_nasabah)));
            
            // Cari realisasi yang namanya sama
            $match = $realisasi->first(function ($r) use ($namaJadwal) {
                $namaReal = strtoupper(trim(preg_replace('/\s+/', ' ', $r->nama_nasabah)));
                return $namaReal === $namaJadwal;
            });

            if ($match) {
                $realisasiTerpakaiIds[] = $match->id; // Tandai ID realisasi ini sudah dipakai
            }

            $dataFinal->push((object)[
                'id' => $j->id,
                'kode_ao' => $myCode,
                'nama_ao' => $karyawan->nama,
                'no_angsuran' => $j->no_angsuran, 
                'nama_nasabah' => $j->nama_nasabah,
                'alamat_nasabah' => $j->alamat_nasabah,
                'nominal' => $j->nominal,
                'sisa_pokok' => $j->sisa_pokok,
                'kol' => $j->kol ?? '-',
                'bulan' => $j->bulan, 
                'is_filled' => $match ? true : false,
                'is_mandiri' => false,
                'id_kunjungan_real' => $match ? $match->id : null // Tambahan untuk link detail
            ]);
        }

        // PROSES B: Loop Data Realisasi (Cek apakah ada yang belum masuk di PROSES A)
        foreach ($realisasi as $r) {
            // HANYA masukkan jika ID realisasi ini BELUM digunakan di PROSES A
            if (!in_array($r->id, $realisasiTerpakaiIds)) {
                $dataFinal->push((object)[
                    'id' => $r->id,
                    'kode_ao' => $r->kode_ao,
                    'nama_nasabah' => $r->nama_nasabah,
                    'kol' => $r->kol ?? '-',
                    'bulan' => $r->created_at ? \Carbon\Carbon::parse($r->created_at)->translatedFormat('F Y') : date('F Y'), 
                    'is_filled' => true,
                    'is_mandiri' => true
                ]);
            }
        }

        // --- LOGIKA MANUAL PAGINATION ---
        $currentPage = Paginator::resolveCurrentPage() ?: 1;
        $perPage = 10; 
        $currentItems = $dataFinal->slice(($currentPage - 1) * $perPage, $perPage)->all();

        $data = new LengthAwarePaginator(
            $currentItems,
            $dataFinal->count(),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );

        if (request()->ajax()) {
            return view('kunjungan.partials.data_table', compact('data'));
        }

        return view('kunjungan.datakunjungan', compact('data'));
    }

    public function laporanKunjunganContent()
    {
        $user = Auth::guard('karyawan')->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $myCode = strtoupper(trim($user->kode_ao));

        $laporan = \DB::table('kunjungans')
            ->leftJoin('nasabahs', 'kunjungans.no_nasabah', '=', 'nasabahs.no_angsuran')
            ->where('kunjungans.kode_ao', 'LIKE', '%' . $myCode . '%')
            ->select(
                'kunjungans.id as id_kunjungan',
                'kunjungans.kode_ao',
                'kunjungans.nama_nasabah',
                'kunjungans.created_at',
                'kunjungans.kol',
                'nasabahs.kol as kol_master'
            )
            ->distinct()
            ->orderBy('kunjungans.created_at', 'desc')
            ->get()
            ->map(function($r) {
                return (object)[
                    'id_kunjungan' => $r->id_kunjungan,
                    'kode_ao'      => $r->kode_ao ?? '-',
                    'nama_nasabah' => $r->nama_nasabah,
                    'kol'          => $r->kol ?: ($r->kol_master ?: '-'),
                    'bulan'        => \Carbon\Carbon::parse($r->created_at)->translatedFormat('F Y'),
                ];
            });

        return view('kunjungan.partials.laporan_table', ['laporan' => $laporan]);
    }

    public function indexpelaporan()
    {
        $user = Auth::guard('karyawan')->user();
        if (!$user) return redirect()->back();

        $myCode = strtoupper(trim($user->kode_ao));

        $laporan = \DB::table('kunjungans')
            // Join menggunakan No Nasabah karena lebih unik dibanding Nama
            ->leftJoin('nasabahs', 'kunjungans.no_nasabah', '=', 'nasabahs.no_angsuran')
            ->where('kunjungans.kode_ao', 'LIKE', '%' . $myCode . '%')
            ->select(
                'kunjungans.id as id_kunjungan', 
                'kunjungans.kode_ao', 
                'kunjungans.nama_nasabah', 
                'kunjungans.created_at',
                'kunjungans.kol',
                'nasabahs.kol as kol_master'
            )
            // Gunakan pembeda unik untuk menghindari duplikasi dari hasil JOIN
            ->distinct() 
            ->orderBy('kunjungans.created_at', 'desc')
            ->get()
            ->map(function($item) {
                return (object)[
                    'id_kunjungan' => $item->id_kunjungan,
                    'kode_ao'      => $item->kode_ao,
                    'nama_nasabah' => $item->nama_nasabah,
                    // Logika: ambil kol dari kunjungan, jika kosong ambil dari master nasabah
                    'kol'          => $item->kol ?: ($item->kol_master ?: '-'), 
                    'bulan'        => \Carbon\Carbon::parse($item->created_at)->translatedFormat('F Y'),
                ];
            });

        return view('kunjungan.partials.laporan_table', ['laporan' => $laporan]);
    }

    public function detailPelaporan(Request $request)
    {
        $id = $request->query('id');

        if (!$id) {
            return "<div class='alert alert-danger'>ID Kunjungan tidak ditemukan dalam request.</div>";
        }

        try {
            $detail = \DB::table('kunjungans')->where('id', $id)->first();

            if (!$detail) {
                return "<div style='padding:50px; text-align:center;'>
                            <h3 style='color:red;'>Data Tidak Ditemukan</h3>
                            <p>ID kunjungan ($id) tidak ada di database.</p>
                            <button onclick=\"loadPage('laporan-kunjungan')\" class='btn btn-primary'>Kembali</button>
                        </div>";
            }

            // Render view bukti_kunjungan
            return view('kunjungan.partials.bukti_kunjungan', compact('detail'))->render();

        } catch (\Exception $e) {
            return "<div class='alert alert-danger'>Terjadi kesalahan: " . $e->getMessage() . "</div>";
        }
    }

    public function showBukti($id)
    {
        try {
            // 1. Gunakan DB Table langsung untuk bypass proteksi Model
            $detail = \DB::table('kunjungans')->where('id', $id)->first();

            // 2. Jika data benar-benar tidak ada di DB
            if (!$detail) {
                return "<div style='padding:50px; text-align:center;'>
                            <h3 style='color:red;'>Data Tidak Ditemukan</h3>
                            <p>ID kunjungan ($id) tidak ada di tabel kunjungans.</p>
                            <button onclick=\"loadPage('laporan-kunjungan')\">Kembali</button>
                        </div>";
            }

            // 3. Render view dengan variabel 'detail'
            if (request()->ajax()) {
                return view('kunjungan.partials.bukti_kunjungan', compact('detail'))->render();
            }

            return view('kunjungan.datakunjungan', [
                'data' => \DB::table('kunjungans')->get(), 
                'content' => view('kunjungan.partials.bukti_kunjungan', compact('detail'))->render()
            ]);

        } catch (\Exception $e) {
            return "<div class='alert alert-danger'>Terjadi kesalahan sistem: " . $e->getMessage() . "</div>";
        }
    }

public function store(Request $request)
{
    $karyawan = Auth::guard('karyawan')->user();

    // 1. Bersihkan input No Nasabah
    $noNasabahInput = trim($request->no_nasabah);

    // 2. Cari Data Nasabah (Master atau Jadwal Admin)
    $nasabahMaster = \DB::table('nasabahs')
        ->where('no_angsuran', $noNasabahInput)
        ->first() ?: \DB::table('data_kunjungan_adms')
        ->where('no_angsuran', $noNasabahInput)
        ->first();

    // 3. Proses Foto & Validasi EXIF (Wajib)
    $daftar_nama_foto = []; 
    if ($request->hasFile('foto_kunjungan')) {
        $files = $request->file('foto_kunjungan');
        $filesArray = is_array($files) ? $files : [$files];

        foreach ($filesArray as $file) {
            $extension = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($extension, ['jpg', 'jpeg'])) {
                return response()->json([
                    'error' => 'Gagal! Foto "' . $file->getClientOriginalName() . '" bukan JPG/JPEG. Sistem hanya menerima format JPG untuk validasi GPS.'
                ], 422);
            }

            $exif = @exif_read_data($file->getRealPath());
            if (!$exif || !isset($exif['GPSLatitude']) || !isset($exif['GPSLongitude'])) {
                return response()->json([
                    'error' => 'Gagal! Foto "' . $file->getClientOriginalName() . '" tidak memiliki data GPS.'
                ], 422);
            }

            if (!isset($exif['DateTimeOriginal'])) {
                return response()->json([
                    'error' => 'Gagal! Foto "' . $file->getClientOriginalName() . '" tidak memiliki data waktu asli.'
                ], 422);
            }

            $nama_unik = time() . '_' . uniqid() . '.' . $extension;
            $file->move(public_path('uploads/kunjungan'), $nama_unik);
            $daftar_nama_foto[] = $nama_unik;
        }
    } else {
        return response()->json(['error' => 'Wajib melampirkan foto kunjungan!'], 422);
    }

    // --- BARU: PROSES BUKTI TRANSFER (Opsional) ---
    $nama_bukti_transfer = null;
    if ($request->hasFile('bukti_transfer')) {
        $fileTf = $request->file('bukti_transfer');
        $extTf = strtolower($fileTf->getClientOriginalExtension());
        
        // Untuk bukti transfer, kita izinkan PNG (biasanya hasil screenshot m-banking)
        if (in_array($extTf, ['jpg', 'jpeg', 'png'])) {
            $nama_bukti_transfer = 'TF_' . time() . '_' . uniqid() . '.' . $extTf;
            $fileTf->move(public_path('uploads/kunjungan'), $nama_bukti_transfer);
        }
    }
    // ----------------------------------------------

    try {
        // 4. Simpan ke Database
        \DB::table('kunjungans')->insert([
            'kode_ao'             => $karyawan->kode_ao,
            'no_nasabah'          => $request->no_nasabah, 
            'nama_nasabah'        => $request->nama_nasabah,
            'kol'                 => $nasabahMaster ? $nasabahMaster->kol : ($request->kol ?: 1),
            'ada_di_lokasi'       => $request->ada_di_lokasi,
            'catatan'             => $request->catatan, 
            'tgl_janji_bayar'     => $request->tgl_janji_bayar,
            'nominal_janji_bayar' => $request->filled('nominal_janji_bayar') 
                                     ? str_replace(['.', ','], '', $request->nominal_janji_bayar) 
                                     : 0,
            'foto_kunjungan'      => json_encode($daftar_nama_foto), 
            
            // --- MASUKKAN KE DB ---
            'bukti_transfer'      => $nama_bukti_transfer, 
            // ----------------------

            'koordinat'           => $request->koordinat, 
            'created_at'          => now(),
        ]);

        return response()->json([
            'success' => 'Laporan berhasil disimpan! ' . count($daftar_nama_foto) . ' foto tervalidasi GPS.'
        ]);

    } catch (\Exception $e) {
        return response()->json(['error' => 'Terjadi kesalahan database: ' . $e->getMessage()], 500);
    }
}
        
    private function getGps($exifCoord, $hemi) 
    {
        $degrees = count($exifCoord) > 0 ? $this->getFraction($exifCoord[0]) : 0;
        $minutes = count($exifCoord) > 1 ? $this->getFraction($exifCoord[1]) : 0;
        $seconds = count($exifCoord) > 2 ? $this->getFraction($exifCoord[2]) : 0;
        
        $flip = ($hemi == 'S' || $hemi == 'W') ? -1 : 1;
        return $flip * ($degrees + ($minutes / 60) + ($seconds / 3600));
    }

    private function getFraction($fraction) 
    {
        $parts = explode('/', $fraction);
        if (count($parts) < 2) return floatval($fraction);
        if (floatval($parts[1]) == 0) return 0;
        return floatval($parts[0]) / floatval($parts[1]);
    }

    public function exportPDF($id)
    {
        // Gunakan DB table agar tidak crash saat relasi dataKunjungan kosong (Data Mandiri)
        $detail = \DB::table('kunjungans')
            ->leftJoin('nasabahs', 'kunjungans.nama_nasabah', '=', 'nasabahs.nasabah')
            ->where('kunjungans.id', $id)
            ->select('kunjungans.*', 'nasabahs.kol as kol_master')
            ->first();

        if (!$detail) return redirect()->back()->with('error', 'Data tidak ditemukan');

        $namaAO = Auth::guard('karyawan')->user()->nama;

        // Tambahkan properti dummy agar view PDF lama tidak error
        $detail->dataKunjungan = (object)[
            'kode_ao'      => $detail->kode_ao,
            'nama_nasabah' => $detail->nama_nasabah,
            'kol'          => $detail->kol ?: ($detail->kol_master ?: '-'),
            'bulan'        => $detail->bulan ?? $detail->created_at
        ];

        $pdf = Pdf::loadView('kunjungan.pdf_gabungan', compact('detail', 'namaAO'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('Laporan_Lengkap_' . str_replace(' ', '_', $detail->nama_nasabah) . '.pdf');
    }

public function exportWord($id)
{
    // 1. Ambil data dengan Join
    $data = \DB::table('kunjungans')
        ->leftJoin('data_kunjungan_adms', function($join) {
            $join->on(\DB::raw('TRIM(kunjungans.nama_nasabah)'), '=', \DB::raw('TRIM(data_kunjungan_adms.nama_nasabah)'));
        })
        ->where('kunjungans.id', $id)
        ->select(
            'kunjungans.id',
            'kunjungans.kode_ao',
            'kunjungans.nama_nasabah',
            'kunjungans.catatan',
            'kunjungans.created_at',
            'data_kunjungan_adms.nominal', 
            'data_kunjungan_adms.sisa_pokok',
            'data_kunjungan_adms.no_angsuran',
            'data_kunjungan_adms.alamat_nasabah' // Pastikan ambil dari tabel ADM
        )
        ->first();

    if (!$data) return redirect()->back()->with('error', 'Data tidak ditemukan');

    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(public_path('templates/template_p2k.docx'));

    // 2. Isi variabel ke Word
    $templateProcessor->setValue('nama_nasabah', strtoupper($data->nama_nasabah ?? '-'));
    
    // Gunakan alamat_nasabah yang berasal dari data_kunjungan_adms
    $templateProcessor->setValue('alamat_nasabah', $data->alamat_nasabah ?? '-');

    $templateProcessor->setValue('kode_ao', $data->kode_ao ?? '-');
    $templateProcessor->setValue('no_angsuran', $data->no_angsuran ?? '-');

    // 3. Format Angka
    $nominalText = number_format($data->nominal ?? 0, 0, ',', '.');
    $sisaText = number_format($data->sisa_pokok ?? 0, 0, ',', '.');

    $templateProcessor->setValue('nominal', $nominalText);
    $templateProcessor->setValue('NOMINAL_VALUE', $nominalText);
    $templateProcessor->setValue('sisa_pokok', $sisaText);
    $templateProcessor->setValue('SISA_VALUE', $sisaText);

    // 4. Proses Download
    $filename = "Tagihan_" . str_replace(' ', '_', $data->nama_nasabah) . ".docx";
    
    if (ob_get_contents()) ob_end_clean();
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    
    $templateProcessor->saveAs('php://output');
    exit;
}

    public function exportExcel()
    {
        $karyawan = Auth::guard('karyawan')->user();
        if (!$karyawan) return redirect()->back();

        $myCode = strtoupper(trim($karyawan->kode_ao));
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\KunjunganExport($myCode), 
            'Laporan_Kunjungan_' . $myCode . '.xlsx'
        );
    }
    

}
