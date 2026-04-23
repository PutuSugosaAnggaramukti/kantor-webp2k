<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use App\Models\DataKunjunganAdm;
use App\Models\Nasabah;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use Carbon\Carbon;

class AdmDokumenController extends Controller
{
  public function dokumenIndex(Request $request) 
{
    // 1. Tambahkan logika pencarian di sini
    $query = Nasabah::query();

    if ($request->has('search') && $request->search != '') {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('nasabah', 'like', '%' . $search . '%')
              ->orWhere('no_angsuran', 'like', '%' . $search . '%')
              ->orWhere('alamat', 'like', '%' . $search . '%');
        });
    }

    // 2. Ambil data dengan pagination dan tetap bawa query string (agar page 2 tidak hilang filternya)
    $dokumen_all = $query->orderBy('nasabah', 'asc')->paginate(10)->withQueryString();

    if ($request->ajax()) {
        return view('admin.partials.dokumen', compact('dokumen_all'))->render();
    }

    $dashboard = new \App\Http\Controllers\dashboard\DashboardAdminController();
    
    try {
        $data = $dashboard->getDashboardData();
    } catch (\Exception $e) {
        $data = [
            'karyawan_count' => \App\Models\Karyawan::count(),
            'title' => 'Dokumen'
        ];
    }

    $data['content'] = view('admin.partials.dokumen', compact('dokumen_all'))->render();
    $data['page'] = 'dokumen'; 
    $data['title'] = 'Data Dokumen';

    return view('admin.datakaryawan', $data);
}
   public function downloadWord($no_angsuran)
    {
        // Cari data nasabah
        $data = Nasabah::where('no_angsuran', $no_angsuran)->firstOrFail();
        
        $templatePath = public_path('templates/Template_p2k.docx');
        
        if (!file_exists($templatePath)) {
            return back()->with('error', "File template tidak ditemukan."); 
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        // --- 1. DATA IDENTITAS (Halaman 1 & 2) ---
        $templateProcessor->setValue('nama_nasabah', strtoupper($data->nasabah));
        $templateProcessor->setValue('alamat_nasabah', $data->alamat);
        $templateProcessor->setValue('no_angsuran', $data->no_angsuran);
        $templateProcessor->setValue('kode', $data->kode ?? '-'); // PG.333 dsb
        $templateProcessor->setValue('rekening', $data->rekening_kredit ?? '-');
        $templateProcessor->setValue('tanggal', Carbon::now()->isoFormat('D MMMM YYYY'));

        // --- 2. DATA KEUANGAN & PERHITUNGAN ---
        $pokokPerBulan = (float) ($data->pokok_per_bulan ?? 0);
        $bungaPerBulan = (float) ($data->bunga_per_bulan ?? 0);
        $denda = (float) ($data->denda ?? 0);
        $totalTagihan = $pokokPerBulan + $bungaPerBulan + $denda;

        // Format Rupiah untuk Halaman 1
        $templateProcessor->setValue('nominal', "Rp " . number_format($data->nominal ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('sisa_pokok', "Rp " . number_format($data->sisa_pokok ?? 0, 0, ',', '.'));
        
        // Variabel Tabel (Bisa dipakai di Halaman 1 & 2)
        $templateProcessor->setValue('pokok_per_bulan', number_format($pokokPerBulan, 0, ',', '.'));
        $templateProcessor->setValue('bunga_per_bulan', number_format($bungaPerBulan, 0, ',', '.'));
        $templateProcessor->setValue('denda', number_format($denda, 0, ',', '.'));
        $templateProcessor->setValue('jumlah_tagihan', number_format($totalTagihan, 0, ',', '.'));

        // --- 3. DATA KHUSUS HALAMAN 2 (LAPORAN KUNJUNGAN) ---
        $templateProcessor->setValue('plafon', number_format($data->nominal ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('agunan', $data->kode_agunan ?? '-');
        $templateProcessor->setValue('pengikatan', $data->ikatan ?? '-');
        
        // Data Tunggakan (Sesuai kolom di Tinker)
        $templateProcessor->setValue('tunggakan_pokok', number_format($data->tunggakan_pokok ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('tunggakan_bunga', number_format($data->tunggakan_bunga ?? 0, 0, ',', '.'));

        // --- 4. PROSES DOWNLOAD ---
        $fileName = 'Surat_Tagihan_' . str_replace(' ', '_', $data->nasabah) . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'PHPWord');
        $templateProcessor->saveAs($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}
