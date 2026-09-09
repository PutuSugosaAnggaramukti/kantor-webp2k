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

    public function __construct($tglAwal = null, $tglAkhir = null) {
        $this->tglAwal = $tglAwal;
        $this->tglAkhir = $tglAkhir;
    }

    public function view(): View
    {
        $query = \DB::table('kunjungans')
            ->leftJoin('data_kunjungan_adms', 'kunjungans.jadwal_id', '=', 'data_kunjungan_adms.id')
            ->leftJoin('nasabahs', 'kunjungans.no_nasabah', '=', 'nasabahs.no_angsuran')
            ->leftJoin('karyawans', 'kunjungans.kode_ao', '=', 'karyawans.kode_ao')
            ->select(
                'kunjungans.no_nasabah',
                'kunjungans.nama_nasabah',
                'kunjungans.kode_ao',
                'kunjungans.catatan',
                'kunjungans.created_at',
                'kunjungans.tgl_janji_bayar',
                'kunjungans.nominal_janji_bayar',
                'nasabahs.kode',
                'nasabahs.rekening_kredit',
                'nasabahs.kode_agunan',
                'nasabahs.ikatan',
                'nasabahs.alamat as alamat_nasabah',
                'nasabahs.sisa_pokok as sisa_pokok_nasabah',
                'nasabahs.pokok_per_bulan as pokok_per_bulan_nasabah',
                'nasabahs.bunga_per_bulan as bunga_per_bulan_nasabah',
                'karyawans.nama as nama_karyawan'
            );

        if ($this->tglAwal && $this->tglAkhir) {
            $query->whereDate('kunjungans.created_at', '>=', $this->tglAwal)
                  ->whereDate('kunjungans.created_at', '<=', $this->tglAkhir);
        }

        $data_kunjungan = $query->orderBy('kunjungans.created_at', 'desc')->get();

        // Group by no_nasabah + kode_ao, gabung catatan
        $grouped = $data_kunjungan->groupBy(function ($item) {
            return $item->no_nasabah . '|' . $item->kode_ao;
        })->map(function ($items) {
            $first = $items->first();

            // Gabungkan semua catatan dengan tanggal
            $catatanList = $items->map(function ($item) {
                $tanggal = \Carbon\Carbon::parse($item->created_at)->locale('id')->translatedFormat('d F Y');
                $catatan = $item->catatan ?? '-';
                return "Tanggal {$tanggal} {$catatan}";
            })->implode("\n");

            // Ambil tgl_janji_bayar & nominal terbaru (dari item terakhir)
            $latest = $items->last();

            return (object) [
                'no_nasabah' => $first->no_nasabah,
                'nama_nasabah' => $first->nama_nasabah,
                'kode' => $first->kode,
                'rekening_kredit' => $first->rekening_kredit,
                'alamat_nasabah' => $first->alamat_nasabah,
                'kode_agunan' => $first->kode_agunan,
                'ikatan' => $first->ikatan,
                'sisa_pokok_nasabah' => $first->sisa_pokok_nasabah,
                'pokok_per_bulan_nasabah' => $first->pokok_per_bulan_nasabah,
                'bunga_per_bulan_nasabah' => $first->bunga_per_bulan_nasabah,
                'kode_ao' => $first->kode_ao,
                'nama_karyawan' => $first->nama_karyawan,
                'catatan_lengkap' => $catatanList,
                'tgl_janji_bayar' => $latest->tgl_janji_bayar,
                'nominal_janji_bayar' => $latest->nominal_janji_bayar,
                'jumlah_kunjungan' => $items->count(),
            ];
        })->values();

        return view('admin.exports.pelaporan_excel', [
            'data_ao' => $grouped,
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
