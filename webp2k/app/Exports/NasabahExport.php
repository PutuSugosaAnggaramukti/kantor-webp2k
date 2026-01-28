<?php

namespace App\Exports;

use App\Models\DataKunjunganAdm;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class NasabahExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $tglAwal, $tglAkhir;

    public function __construct($tglAwal, $tglAkhir)
    {
        $this->tglAwal = $tglAwal;
        $this->tglAkhir = $tglAkhir;
    }

    public function query()
    {
        // Query mengambil data kunjungan nasabah dalam rentang tanggal
        return DataKunjunganAdm::query()
            ->with('karyawan')
            ->whereBetween('tanggal', [$this->tglAwal, $this->tglAkhir])
            ->orderBy('nama_nasabah', 'asc');
    }

    public function headings(): array
    {
        return [
            'No',
            'No Angsuran',
            'Nama Nasabah',
            'Alamat',
            'Tanggal Kunjungan',
            'Nama AO (Pengunjung)',
            'Status/Kol'
        ];
    }

    public function map($row): array
    {
        static $no = 1;
        return [
            $no++,
            $row->no_angsuran,
            $row->nama_nasabah,
            $row->alamat_nasabah,
            $row->tanggal,
            $row->karyawan->nama ?? 'N/A',
            $row->kol,
        ];
    }
}