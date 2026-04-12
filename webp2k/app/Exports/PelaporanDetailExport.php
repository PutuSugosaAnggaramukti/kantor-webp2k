<?php

namespace App\Exports;

use App\Models\DataKunjunganAdm;
use App\Models\Nasabah;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting; // Tambahkan ini
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;    // Tambahkan ini

class PelaporanDetailExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $id;
    private $rowNumber = 0;

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Mengambil data kunjungan khusus untuk AO tersebut
     */
    public function query()
    {
        return DataKunjunganAdm::where('karyawan_id', $this->id)
            ->orWhere('kode_ao', $this->id)
            ->orderBy('nama_nasabah', 'asc')
            ->orderBy('tanggal', 'desc');
    }

    /**
     * Header Tabel Excel
     */
    public function headings(): array
    {
        return [
            'No',
            'Tanggal Kunjung',
            'No. Angsuran',
            'A',
            'Kode AO',
            'Nama Nasabah',
            'Alamat',
            'Nominal',
            'Sisa Pokok',
            'Pokok/bln',
            'Bunga/bln'
        ];
    }

    /**
     * Mapping data agar rapi di Excel
     * Kita kirim angka polos, biar Excel yang memformat lewat WithColumnFormatting
     */
    public function map($kunjungan): array
    {
        $this->rowNumber++;

        $nasabah = Nasabah::where('no_angsuran', $kunjungan->no_angsuran)->first();

        return [
            $this->rowNumber,
            $kunjungan->tanggal ? \Carbon\Carbon::parse($kunjungan->tanggal)->format('d-m-Y') : '-',
            $kunjungan->no_angsuran,
            $nasabah->kode_agunan ?? '-',
            $nasabah->kode_ao_nasabah ?? '-',
            $kunjungan->nama_nasabah,
            $kunjungan->alamat_nasabah,
            
            // Kirim angka mentah yang sudah dibulatkan (tanpa number_format di sini)
            round($kunjungan->nominal ?? 0),
            round($nasabah->sisa_pokok ?? 0),
            round($nasabah->pokok_per_bulan ?? 0),
            round($nasabah->bunga_per_bulan ?? 0),
        ];
    }

    /**
     * Format kolom agar menggunakan pemisah ribuan titik tanpa desimal
     */
    public function columnFormats(): array
    {
        return [
            'H' => '#,##0', // Nominal
            'I' => '#,##0', // Sisa Pokok
            'J' => '#,##0', // Pokok/bln
            'K' => '#,##0', // Bunga/bln
        ];
    }

    /**
     * Memberikan style (Bolding) pada header
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}