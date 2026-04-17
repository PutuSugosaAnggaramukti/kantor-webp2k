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
        // PERUBAHAN DI SINI: Cari berdasarkan no_angsuran di tabel Nasabah
        $data = Nasabah::where('no_angsuran', $no_angsuran)->firstOrFail();
        
        $templatePath = public_path('templates/Template_p2k.docx');
        
        if (!file_exists($templatePath)) {
            dd("File TIDAK ditemukan di: " . $templatePath); 
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        // Isi variabel template
        $templateProcessor->setValue('nama_nasabah', strtoupper($data->nasabah));
        $templateProcessor->setValue('alamat_nasabah', $data->alamat);
        $templateProcessor->setValue('no_angsuran', $data->no_angsuran);
        $templateProcessor->setValue('kode_ao', $data->kode_ao ?? '-');
        
        // Gunakan tanggal hari ini untuk surat tagihan
        $templateProcessor->setValue('tanggal', Carbon::now()->format('d-m-Y'));

        // Format Rupiah
        $nominalFormat = "Rp " . number_format($data->nominal ?? 0, 0, ',', '.') . ",-";
        $sisaFormat = "Rp " . number_format($data->sisa_pokok ?? 0, 0, ',', '.') . ",-";

        $templateProcessor->setValue('nominal', $nominalFormat);
        $templateProcessor->setValue('sisa_pokok', $sisaFormat);

        $fileName = 'Surat_Tagihan_' . str_replace(' ', '_', $data->nasabah) . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'PHPWord');
        $templateProcessor->saveAs($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }
}
