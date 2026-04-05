<?php

namespace App\Exports;

use App\Models\DataKunjunganAdm;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PelaporanDetailExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
            'Nama Nasabah',
            'Alamat',
            'Nominal',
            'Sisa Pokok',
            'KOL'
        ];
    }

    /**
     * Mapping data agar rapi di Excel
     */
    public function map($kunjungan): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $kunjungan->tanggal ? $kunjungan->tanggal->format('d-m-Y') : '-',
            $kunjungan->no_angsuran,
            $kunjungan->nama_nasabah,
            $kunjungan->alamat_nasabah,
            $kunjungan->nominal,
            $kunjungan->sisa_pokok,
            $kunjungan->kol,
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