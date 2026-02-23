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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KunjunganExport;

class KunjunganController extends Controller
{

    public function index()
    {
        return $this->getMergedKunjunganData();
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
        
        // Ambil Jadwal dari Admin
        $jadwal = DataKunjunganAdm::where('karyawan_id', $karyawan->id)->get();

        // Ambil Realisasi (Termasuk Heni / Data Mandiri)
        $realisasi = \DB::table('kunjungans')
            ->where('kode_ao', 'LIKE', '%' . $myCode . '%')
            ->get();

        $dataFinal = collect();
        $namaTerproses = [];

        // PROSES A: Masukkan data sesuai Jadwal Admin
       foreach ($jadwal as $j) {
            $namaJadwal = strtoupper(trim(preg_replace('/\s+/', ' ', $j->nama_nasabah)));
            
            $match = $realisasi->first(function ($r) use ($namaJadwal) {
                $namaReal = strtoupper(trim(preg_replace('/\s+/', ' ', $r->nama_nasabah)));
                return $namaReal === $namaJadwal;
            });

            $dataFinal->push((object)[
                'id' => $j->id,
                'kode_ao' => $myCode,
                'nama_ao' => $karyawan->nama,
                'no_angsuran' => $j->no_angsuran, 
                'nama_nasabah' => $j->nama_nasabah,
                'alamat_nasabah' => $j->alamat_nasabah, // TAMBAHKAN INI
                'nominal' => $j->nominal, // TAMBAHKAN INI
                'sisa_pokok' => $j->sisa_pokok, // TAMBAHKAN INI
                'kol' => $j->kol ?? '-',
                'bulan' => $j->bulan, 
                'is_filled' => $match ? true : false,
                'is_mandiri' => false
            ]);
            
            $namaTerproses[] = $namaJadwal;
        }

        // PROSES B: Masukkan data Mandiri (Nasabah yang tidak ada di jadwal)
        foreach ($realisasi as $r) {
            $namaReal = strtoupper(trim(preg_replace('/\s+/', ' ', $r->nama_nasabah)));
            
            if (!in_array($namaReal, $namaTerproses)) {
                $dataFinal->push((object)[
                    'id' => $r->id,
                    'kode_ao' => $r->kode_ao,
                    'nama_nasabah' => $r->nama_nasabah,
                    'kol' => $r->kol ?? '-',
                    'bulan' => $r->created_at ? \Carbon\Carbon::parse($r->created_at)->translatedFormat('F Y') : date('F Y'), 
                    'is_filled' => true,
                    'is_mandiri' => true
                ]);
                $namaTerproses[] = $namaReal;
            }
        }

        $data = $dataFinal->values();

        // CEK: Jika request datang dari AJAX (pindah menu), kirim POTONGAN tabel saja
        if (request()->ajax()) {
            return view('kunjungan.partials.data_table', compact('data'));
        }

        // Jika akses awal, kirim HALAMAN UTUH
        return view('kunjungan.datakunjungan', compact('data'));
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
    $user = Auth::guard('karyawan')->user();
    if (!$user) return redirect()->back();

    $myCode = strtoupper(trim($user->kode_ao));

    $laporan = \DB::table('kunjungans')
        ->leftJoin('nasabahs', function($join) {
            $join->on('kunjungans.nama_nasabah', '=', 'nasabahs.nasabah');
        })
        ->where('kunjungans.kode_ao', 'LIKE', '%' . $myCode . '%')
        ->select(
            'kunjungans.*', 
            'nasabahs.kol as kol_master' 
        )
        ->orderBy('kunjungans.created_at', 'desc')
        ->get()
        ->map(function($item) {
            return (object)[
                'id_kunjungan' => $item->id,
                'kode_ao'      => $item->kode_ao,
                'nama_nasabah' => $item->nama_nasabah,
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

        // 1. Cek Duplikasi
        $exists = \DB::table('kunjungans')
            ->where('kode_ao', $karyawan->kode_ao)
            ->where('nama_nasabah', $request->nama_nasabah)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Data kunjungan untuk nasabah ini sudah dilaporkan!');
        }

        // 2. Proses Upload Foto
        $nama_file_foto = null;
        if ($request->hasFile('foto_kunjungan')) {
            $file = $request->file('foto_kunjungan');
            $nama_file_foto = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/kunjungan'), $nama_file_foto);
        }

        // 3. Simpan ke Database
        \DB::table('kunjungans')->insert([
            'kode_ao'         => $karyawan->kode_ao,
            'no_nasabah'      => $request->no_nasabah, // Ambil dari input form, bukan kode AO
            'nama_nasabah'    => $request->nama_nasabah,
            'kol'             => $request->kol,        // MASUKKAN KOL (OPSIONAL)
            'ada_di_lokasi'   => $request->ada_di_lokasi,
            'catatan'         => $request->catatan, 
            'tgl_janji_bayar' => $request->tgl_janji_bayar,
            'foto_kunjungan'  => $nama_file_foto, 
            'koordinat'       => $request->koordinat, 
            'created_at'      => now(),
        ]);

        return redirect()->back()->with('success', 'Laporan kunjungan berhasil disimpan!');
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
    // 1. Ambil data (Nama, Alamat, dan Nominal sudah cocok di aplikasi)
    $data = \DB::table('kunjungans')
        ->leftJoin('data_kunjungan_adms', function($join) {
            $join->on(\DB::raw('TRIM(kunjungans.nama_nasabah)'), '=', \DB::raw('TRIM(data_kunjungan_adms.nama_nasabah)'));
        })
        ->where('kunjungans.id', $id)
        ->select(
            'kunjungans.*', 
            'data_kunjungan_adms.nominal', 
            'data_kunjungan_adms.sisa_pokok',
            'data_kunjungan_adms.no_angsuran'
        )
        ->first();

    if (!$data) return redirect()->back()->with('error', 'Data tidak ditemukan');
 
    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(public_path('templates/template_p2k.docx'));

    // 2. Isi variabel yang sudah berhasil sebelumnya
    $templateProcessor->setValue('nama_nasabah', strtoupper($data->nama_nasabah ?? '-'));
    $templateProcessor->setValue('alamat_nasabah', $data->alamat_nasabah ?? '-');
    $templateProcessor->setValue('kode_ao', $data->kode_ao ?? '-');
    $templateProcessor->setValue('no_angsuran', $data->no_angsuran ?? '-');

    // 3. PEMAKSAAN VARIABEL BARU (Agar terhindar dari error XML Word)
    // Pastikan di file Word kamu sudah diganti menjadi NOMINAL_VALUE dan SISA_VALUE
    $nominalText = number_format($data->nominal ?? 0, 0, ',', '.');
    $sisaText = number_format($data->sisa_pokok ?? 0, 0, ',', '.');

    // Kita tembak ke 3 kemungkinan nama variabel sekaligus untuk berjaga-jaga
    $templateProcessor->setValue('nominal', $nominalText); // Jika di word: ${nominal}
    $templateProcessor->setValue('NOMINAL_VALUE', $nominalText); // Jika di word: NOMINAL_VALUE
    
    $templateProcessor->setValue('sisa_pokok', $sisaText); // Jika di word: ${sisa_pokok}
    $templateProcessor->setValue('SISA_VALUE', $sisaText); // Jika di word: SISA_VALUE

    // 4. Download
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
