<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PelaporanExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $tglAwal, $tglAkhir;

    public function __construct($tglAwal, $tglAkhir) {
        $this->tglAwal = $tglAwal;
        $this->tglAkhir = $tglAkhir;
    }

    public function view(): View
    {
        $data_kunjungan = \DB::table('kunjungans')
            ->leftJoin('data_kunjungan_adms', 'kunjungans.jadwal_id', '=', 'data_kunjungan_adms.id')
            ->leftJoin('nasabahs', 'kunjungans.no_nasabah', '=', 'nasabahs.no_angsuran')
            ->leftJoin('karyawans', 'kunjungans.kode_ao', '=', 'karyawans.kode_ao')
            ->whereDate('kunjungans.created_at', '>=', $this->tglAwal)
            ->whereDate('kunjungans.created_at', '<=', $this->tglAkhir)
            ->select(
                'kunjungans.*',
                'data_kunjungan_adms.tanggal as tanggal_jadwal',
                'data_kunjungan_adms.bulan',
                'nasabahs.no_angsuran',
                'nasabahs.kode',
                'nasabahs.rekening_kredit',
                'nasabahs.kode_agunan',
                'nasabahs.ikatan',
                'nasabahs.alamat as alamat_nasabah',
                'nasabahs.kode_ao_nasabah',
                'nasabahs.sisa_pokok as sisa_pokok_nasabah',
                'nasabahs.pokok_per_bulan as pokok_per_bulan_nasabah',
                'nasabahs.bunga_per_bulan as bunga_per_bulan_nasabah',
                'karyawans.nama as nama_karyawan'
            )
            ->orderBy('kunjungans.created_at', 'desc')
            ->get();

        return view('admin.exports.pelaporan_excel', [
            'data_ao' => $data_kunjungan,
            'tglAwal' => $this->tglAwal,
            'tglAkhir' => $this->tglAkhir
        ]);
    }

   public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // I = Sisa Pokok, J = Pokok/bln, K = Bunga/bln
        $sheet->getStyle("I5:K{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode('#,##0');
    }
}