<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\DataKunjunganAdm;
use App\Models\HasilKunjungan;
use App\Models\Nasabah;
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
        $karyawanId = Auth::guard('karyawan')->id();
        
        if (!$karyawanId) {
            return "<div class='alert alert-warning'>Sesi berakhir, silakan refresh halaman.</div>";
        }
    
        $data = DataKunjunganAdm::where('karyawan_id', $karyawanId)->get();
        if (request()->ajax()) {
            return view('kunjungan.partials.data_table', compact('data'));
        }
        return view('kunjungan.datakunjungan', compact('data'));
    }

    public function dataKunjunganContent()
    {
        $karyawanId = Auth::guard('karyawan')->id();
        $data = DataKunjunganAdm::where('karyawan_id', $karyawanId)->get();
    
        return view('kunjungan.partials.data_table', compact('data'));
    }

    public function laporanKunjunganContent()
    {
        $data = Nasabah::all();
        return view('kunjungan.partials.laporan_table', compact('data'));
    }

    public function indexpelaporan()
    {
        try {
            // 1. Ambil ID Karyawan (AO) yang sedang login
            $karyawanId = Auth::guard('karyawan')->id();
    
            // 2. Filter data agar hanya mengambil milik AO yang login (karyawan_id)
            $laporan = DataKunjunganAdm::where('karyawan_id', $karyawanId)
                        ->with(['karyawan', 'hasilKunjungan'])
                        ->get();
    
            return view('kunjungan.partials.laporan_table', compact('laporan'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function detailPelaporan(Request $request)
    {
        $id = $request->query('id');
        $karyawanId = Auth::guard('karyawan')->id();

        // Cari detail laporan, tapi pastikan data jadwalnya (dataKunjungan) milik AO yang login
        $detail = HasilKunjungan::whereHas('dataKunjungan', function($query) use ($karyawanId) {
            $query->where('karyawan_id', $karyawanId);
        })->find($id);

        if (!$detail) {
            return "<div class='alert alert-danger'>Akses ditolak atau data tidak ditemukan.</div>";
        }

        return view('kunjungan.partials.bukti_kunjungan', compact('detail'));
    }

   public function showBukti($id)
    {
        $dataKunjungan = Kunjungan::findOrFail($id);

        if (request()->ajax()) {
            return view('kunjungan.partials.bukti_kunjungan', compact('dataKunjungan'));
        }

        return view('kunjungan.datakunjungan', [
            'data' => Kunjungan::all(), 
            'content' => view('kunjungan.partials.bukti_kunjungan', compact('dataKunjungan'))->render()
        ]);
    }

    public function store(Request $request)
    {
        // 1. Definisikan variabel dengan nilai awal null agar tidak error jika foto kosong
        $nama_file_foto = null;

        // 2. Logika Upload Foto
        if ($request->hasFile('foto_kunjungan')) {
            $file = $request->file('foto_kunjungan');
            
            // Buat nama unik agar tidak bentrok (contoh: 17123456.jpg)
            $nama_file_foto = time() . '.' . $file->getClientOriginalExtension();
            
            // Simpan ke folder public/uploads/kunjungan
            $file->move(public_path('uploads/kunjungan'), $nama_file_foto);
        }

        // 3. Simpan ke Database menggunakan Model Kunjungan
        Kunjungan::create([
            'kode_ao'            => auth()->user()->kode_ao,
            'no_nasabah'         => $request->no_nasabah,
            'nama_nasabah'       => $request->nama_nasabah,
            'ada_di_lokasi'      => $request->ada_di_lokasi,
            'keterangan_nasabah' => $request->keterangan_nasabah,
            'catatan'            => $request->catatan,
            'foto_kunjungan'     => $nama_file_foto, // Sekarang variabel ini sudah ada
            'koordinat'          => $request->koordinat,
        ]);

        return redirect()->back()->with('success', 'Data kunjungan berhasil disimpan!');
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
        $textTable->addCell(7000)->addText(": " . $detail->created_at->format('d/m/Y H:i') . " WIB");
        
        $textTable->addRow();
        $textTable->addCell(2500)->addText("Koordinat Lokasi");
        $textTable->addCell(7000)->addText(": " . $detail->koordinat);
        
        $textTable->addRow();
        $textTable->addCell(2500)->addText("Catatan AO");
        $textTable->addCell(7000)->addText(": " . $detail->catatan);

        if ($detail->foto_kunjungan) {
            $section->addTextBreak(1);
            $section->addText("Dokumentasi Foto:", ['bold' => true]);
            $path = public_path('storage/' . $detail->foto_kunjungan);
            if (file_exists($path)) {
                $section->addImage($path, [
                    'width' => 280, 
                    'height' => 200, 
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
                ]);
            }
        }

        $section->addTextBreak(2);
        $section->addText("Dicetak pada: " . date('d/m/Y H:i:s'), ['size' => 9], ['alignment' => 'right']);
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
