<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\DataKunjunganAdm;
use App\Models\HasilKunjungan;
use App\Models\Nasabah;
use App\Helpers\ExifHelper;
use App\Models\Karyawan;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

class KunjunganController extends Controller
{
   public function index()
    {
        $karyawan = Auth::guard('karyawan')->user();
        
        if (!$karyawan) {
            return "<div class='alert alert-warning'>Sesi berakhir, silakan refresh halaman.</div>";
        }

        $data = DataKunjunganAdm::where('karyawan_id', $karyawan->id)
            ->get()
            ->map(function ($item) use ($karyawan) {
                // Cek berdasarkan kode_ao dan nama_nasabah sesuai tabel kunjungans di screenshot
                $item->is_filled = \DB::table('kunjungans')
                    ->where('kode_ao', $karyawan->kode_ao) 
                    ->where('nama_nasabah', $item->nama_nasabah)
                    ->exists();
                return $item;
            });

        if (request()->ajax()) {
            return view('kunjungan.partials.data_table', compact('data'));
        }
        return view('kunjungan.datakunjungan', compact('data'));
    }

   public function dataKunjunganContent()
    {
        $karyawan = Auth::guard('karyawan')->user();
        $data = DataKunjunganAdm::where('karyawan_id', $karyawan->id)->get();

        $data->map(function ($item) use ($karyawan) {
            $item->is_filled = \DB::table('kunjungans')
                ->where('kode_ao', $karyawan->kode_ao)
                ->where('nama_nasabah', $item->nama_nasabah)
                ->exists();
            return $item;
        });

        // Pastikan compact('data') dikirim ke view
        return view('kunjungan.partials.data_table', compact('data'));
    }

    public function laporanKunjunganContent()
    {
        $jadwal = \DB::table('nasabahs')->get();
        $realisasi = \DB::table('kunjungans')->get();

        $laporan = [];

        foreach ($jadwal as $j) {
            $kunjungan = $realisasi->first(function ($value) use ($j) {
                return strtoupper(trim($value->nama_nasabah)) === strtoupper(trim($j->nasabah));
            });

            $laporan[] = (object)[
                'id_kunjungan' => $kunjungan ? $kunjungan->id : null,
                'kode_ao'      => $j->kode_ao,
                'nama_nasabah' => $j->nasabah, 
                'kol'          => $j->kol,
                'bulan'        => $j->bulan,
            ];
        }

        foreach ($realisasi as $r) {
            $adaDiJadwal = $jadwal->first(function ($value) use ($r) {
                return strtoupper(trim($value->nasabah)) === strtoupper(trim($r->nama_nasabah));
            });

            if (!$adaDiJadwal) {
                $laporan[] = (object)[
                    'id_kunjungan' => $r->id,
                    'kode_ao'      => $r->kode_ao ?? '-',
                    'nama_nasabah' => $r->nama_nasabah,
                    'kol'          => '-',
                    'bulan'        => \Carbon\Carbon::parse($r->created_at)->format('Y-m'),
                ];
            }
        }

        return view('kunjungan.partials.laporan_table', ['laporan' => $laporan]);
    }

    public function indexpelaporan()
    {
        try {
            $user = Auth::guard('karyawan')->user();
            // Wahyu kodenya PG.822, kita pastikan ini benar
            $myCode = ($user->username == 'WAHYU' || $user->username == 'wahyu') ? 'PG.822' : $user->username;

            // 1. Ambil data realisasi milik Wahyu (Ini pondasi kita)
            $realisasi = \DB::table('kunjungans')
                ->where('kode_ao', $myCode)
                ->get();

            // Ambil daftar nama nasabah yang sudah dikunjungi Wahyu
            $namaSudahDikunjungi = $realisasi->pluck('nama_nasabah')->map(function($nama) {
                return strtoupper(trim($nama));
            })->toArray();

            // 2. Ambil data jadwal (HANYA nasabah yang kodenya PG.822 ATAU yang kodenya kosong)
            $jadwal = \DB::table('nasabahs')
                ->where(function($q) use ($myCode) {
                    $q->where('kode_ao', $myCode)
                    ->orWhere('kode_ao', '')
                    ->orWhereNull('kode_ao')
                    ->orWhere('kode_ao', '-');
                })
                ->get();

            $laporan = [];

            // LANGKAH A: Map data Jadwal
            foreach ($jadwal as $j) {
                $namaJadwalClean = strtoupper(trim($j->nasabah));
                
                // Cari apakah nasabah ini ada di realisasi Wahyu
                $kunjungan = $realisasi->first(function ($value) use ($namaJadwalClean) {
                    return strtoupper(trim($value->nama_nasabah)) === $namaJadwalClean;
                });

                // FILTER: Tampilkan nasabah jika:
                // 1. Sudah dikunjungi Wahyu, ATAU
                // 2. Memang jadwalnya Wahyu (PG.822), ATAU
                // 3. Nasabah umum yang kodenya kosong (agar Wahyu bisa lihat jadwalnya)
                if ($kunjungan || $j->kode_ao == $myCode || empty($j->kode_ao) || $j->kode_ao == '-') {
                    
                    // Batasi nasabah umum agar tidak ribuan (Hanya tampilkan 15 nasabah umum pertama + yang sudah dikunjungi)
                    if (!$kunjungan && empty($j->kode_ao) && count($laporan) > 15) continue;

                    $laporan[] = (object)[
                        'id_kunjungan' => $kunjungan ? $kunjungan->id : null,
                        'kode_ao'      => $myCode, // Paksa tampilkan kode Wahyu agar tidak '-'
                        'nama_nasabah' => $j->nasabah,
                        'kol'          => $j->kol,
                        'bulan'        => $j->bulan,
                    ];
                }
            }

            // LANGKAH B: Tambahkan Mandiri (Ingram) yang mungkin tidak ada di filter jadwal di atas
            foreach ($realisasi as $r) {
                $namaRealClean = strtoupper(trim($r->nama_nasabah));
                $adaDiList = collect($laporan)->first(function($item) use ($namaRealClean) {
                    return strtoupper(trim($item->nama_nasabah)) === $namaRealClean;
                });

                if (!$adaDiList) {
                    $laporan[] = (object)[
                        'id_kunjungan' => $r->id,
                        'kode_ao'      => $r->kode_ao,
                        'nama_nasabah' => $r->nama_nasabah,
                        'kol'          => '-',
                        'bulan'        => \Carbon\Carbon::parse($r->created_at)->format('Y-m'),
                    ];
                }
            }

            // Urutkan berdasarkan status kunjungan (yang hijau di atas) lalu nama
            $laporan = collect($laporan)->sortByDesc('id_kunjungan')->values()->all();

            return view('kunjungan.partials.laporan_table', ['laporan' => $laporan]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function detailPelaporan(Request $request)
    {
        // Ambil ID dari query string (?id=...)
        $id = $request->query('id');

        if (!$id) {
            return "<div class='alert alert-danger'>ID Kunjungan tidak ditemukan dalam request.</div>";
        }

        try {
            // Gunakan DB Table langsung agar bypass proteksi Model
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

        // Pastikan pengecekan menggunakan kolom yang benar-benar ada di tabel
        $exists = \DB::table('kunjungans')
            ->where('kode_ao', $karyawan->kode_ao)
            ->where('nama_nasabah', $request->nama_nasabah)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Data sudah ada!');
        }

        $nama_file_foto = null;
        if ($request->hasFile('foto_kunjungan')) {
            $file = $request->file('foto_kunjungan');
            $nama_file_foto = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/kunjungan'), $nama_file_foto);
        }

        // Sesuaikan dengan struktur tabel di gambar: id, kode_ao, no_nasabah, nama_nasabah, dll.
        \DB::table('kunjungans')->insert([
            'kode_ao'         => $karyawan->kode_ao,
            'no_nasabah'      => $karyawan->kode_ao, // Di gambar DB, no_nasabah diisi PG 822
            'nama_nasabah'    => $request->nama_nasabah,
            'ada_di_lokasi'   => $request->ada_di_lokasi,
            'catatan'         => $request->catatan, 
            'tgl_janji_bayar' => $request->tgl_janji_bayar,
            'foto_kunjungan'  => $nama_file_foto, 
            'koordinat'       => $request->koordinat, 
            'created_at'      => now(),
        ]);

        return redirect()->back()->with('success', 'Berhasil disimpan!');
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
        $detail = HasilKunjungan::with('dataKunjungan')->findOrFail($id);
        $namaAO = Auth::guard('karyawan')->user()->nama; 
        $pdf = Pdf::loadView('kunjungan.pdf_gabungan', compact('detail', 'namaAO'));

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('Laporan_Lengkap_' . $detail->dataKunjungan->nama_nasabah . '.pdf');
    }

   public function exportWord($id)
    {
        $detail = HasilKunjungan::with('dataKunjungan')->findOrFail($id);
        $namaAO = Auth::guard('karyawan')->user()->nama;

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        
        $phpWord->setDefaultFontName('Helvetica');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection();

        $section->addText("LAPORAN HASIL KUNJUNGAN", ['bold' => true, 'size' => 14], ['alignment' => 'center']);
        $section->addText("Bagian P2K - AO: " . strtoupper($namaAO), ['bold' => true], ['alignment' => 'center']);
        $section->addText("Bank Bantul - Sistem Informasi P2K", ['size' => 10], ['alignment' => 'center']);
        $section->addLine(['width' => 450, 'height' => 0, 'color' => '000000']); 
        $section->addTextBreak(1);

        $section->addText("I. RINGKASAN DATA NASABAH", ['bold' => true], ['shading' => ['fill' => 'F2F2F2']]);
        
        $tableStyle = ['borderSize' => 6, 'borderColor' => '999999', 'cellMargin' => 80];
        $phpWord->addTableStyle('RingkasanTable', $tableStyle);
        $table = $section->addTable('RingkasanTable');
        
        $table->addRow();
        $table->addCell(2000, ['bgColor' => 'F8F9FA'])->addText("KODE AO", ['bold' => true, 'size' => 9], ['alignment' => 'center']);
        $table->addCell(4000, ['bgColor' => 'F8F9FA'])->addText("NAMA NASABAH", ['bold' => true, 'size' => 9], ['alignment' => 'center']);
        $table->addCell(1500, ['bgColor' => 'F8F9FA'])->addText("KOL", ['bold' => true, 'size' => 9], ['alignment' => 'center']);
        $table->addCell(2000, ['bgColor' => 'F8F9FA'])->addText("BULAN", ['bold' => true, 'size' => 9], ['alignment' => 'center']);
        $table->addCell(1500, ['bgColor' => 'F8F9FA'])->addText("STATUS", ['bold' => true, 'size' => 9], ['alignment' => 'center']);

        $table->addRow();
        $table->addCell(2000)->addText($detail->dataKunjungan->kode_ao, ['size' => 9], ['alignment' => 'center']);
        $table->addCell(4000)->addText($detail->dataKunjungan->nama_nasabah, ['size' => 9]);
        $table->addCell(1500)->addText($detail->dataKunjungan->kol, ['size' => 9], ['alignment' => 'center']);
        $table->addCell(2000)->addText(\Carbon\Carbon::parse($detail->dataKunjungan->bulan)->translatedFormat('F Y'), ['size' => 9], ['alignment' => 'center']);
        $table->addCell(1500)->addText("SELESAI", ['bold' => true, 'color' => '28A745', 'size' => 9], ['alignment' => 'center']);

        $section->addTextBreak(1);

        $section->addText("II. DETAIL HASIL KUNJUNGAN LAPANGAN", ['bold' => true], ['shading' => ['fill' => 'F2F2F2']]);
        
        $textTable = $section->addTable();
        $textTable->addRow();
        $textTable->addCell(2500)->addText("Tanggal Input");
        $textTable->addCell(7000)->addText(": " . \Carbon\Carbon::parse($detail->created_at)->locale('id')->translatedFormat('l, d F Y'));
        
        $textTable->addRow();
        $textTable->addCell(2500)->addText("Koordinat Lokasi");
        $textTable->addCell(7000)->addText(": " . $detail->koordinat);
        
        $textTable->addRow();
        $textTable->addCell(2500)->addText("Catatan AO");
        $textTable->addCell(7000)->addText(": " . $detail->catatan);

       if ($detail->foto_kunjungan) {
            $section->addTextBreak(1);
            $section->addText("Dokumentasi Foto:", ['bold' => true]);
            
            // Perbaikan path: arahkan ke folder 'uploads/kunjungan'
            $path = public_path('uploads/kunjungan/' . $detail->foto_kunjungan);
            
            if (file_exists($path)) {
                $section->addImage($path, [
                    'width'     => 280, 
                    'height'    => 200, 
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
                ]);
            } else {
                // Tambahkan keterangan jika file tidak ditemukan secara fisik di folder
                $section->addText("(File foto tidak ditemukan di server)", ['italic' => true, 'color' => 'FF0000']);
            }
        }
        
        $section->addTextBreak(2);
        $section->addText(\Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y'), ['size' => 10], ['alignment' => 'right']);
        $section->addTextBreak(2);
        $section->addText("( " . strtoupper($namaAO) . " )", ['bold' => true, 'underline' => 'single'], ['alignment' => 'right']);
        $section->addText("Account Officer", [], ['alignment' => 'right']);

        $filename = "Laporan_Lengkap_" . str_replace(' ', '_', $detail->dataKunjungan->nama_nasabah) . ".docx";
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save('php://output');
        exit;
    }
    

}
