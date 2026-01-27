<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use App\Models\DataKunjunganAdm;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use Carbon\Carbon;

class AdmDokumenController extends Controller
{
    public function dokumenIndex() {
        $dokumen_all = DataKunjunganAdm::orderBy('tanggal', 'desc')->get();
        return view('admin.partials.dokumen', compact('dokumen_all'));
    }

   public function downloadWord($id)
    {
        $data = DataKunjunganAdm::with('karyawan')->findOrFail($id);
        $templatePath = public_path('templates/Template_p2k.docx');
        
        if (!file_exists($templatePath)) {
            dd("File TIDAK ditemukan di: " . $templatePath); 
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        // Mapping data
        $templateProcessor->setValue('nama_nasabah', strtoupper($data->nama_nasabah));
        $templateProcessor->setValue('alamat_nasabah', $data->alamat_nasabah);
        $templateProcessor->setValue('no_angsuran', $data->no_angsuran);
        $templateProcessor->setValue('kode_ao', $data->karyawan->kode_ao ?? $data->kode_ao);
        $templateProcessor->setValue('tanggal', Carbon::parse($data->tanggal)->format('d-m-Y'));

        $fileName = 'Dokumen_' . str_replace(' ', '_', $data->nama_nasabah) . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'PHPWord');
        $templateProcessor->saveAs($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }
}
