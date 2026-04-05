<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KunjunganExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $kode_ao;

    public function __construct($kode_ao = null)
    {
        $this->kode_ao = $kode_ao;
    }

    public function collection()
    {
        $query = DB::table('kunjungans')
            // Join ke nasabahs untuk ambil No Angsuran & Karyawans untuk Nama AO
            ->leftJoin('nasabahs', 'kunjungans.no_nasabah', '=', 'nasabahs.no_angsuran')
            ->leftJoin('karyawans', 'kunjungans.kode_ao', '=', 'karyawans.kode_ao')
            ->select('kunjungans.*', 'karyawans.nama as nama_ao')
            ->orderBy('kunjungans.created_at', 'asc'); // Urutan dari yang terlama ke terbaru

        if (!empty($this->kode_ao)) {
            $query->where('kunjungans.kode_ao', 'LIKE', '%' . $this->kode_ao . '%');
        }

        return $query->get();
    }

    public function headings(): array
    {
        // Sesuai urutan gambar: No, Kode, No.Ang, Nama, Kode AO, AO, Ket, Status
        return [
            'No',
            'Kode',
            'No.Ang',
            'Nama',
            'Kode AO',
            'AO',
            'Ket',
            'Status / JB'
        ];
    }

    public function map($row): array
    {
        static $no = 1;

        // Logika Status Berdasarkan Input Lapangan
        $statusJB = '-';
        if ($row->ada_di_lokasi == 'tidak') {
            $statusJB = 'TDK BERTEMU';
        } else {
            if ($row->nominal_janji_bayar > 0) {
                $tgl = $row->tgl_janji_bayar ? \Carbon\Carbon::parse($row->tgl_janji_bayar)->format('d/m/y') : '';
                $statusJB = 'JANJI BAYAR (' . $tgl . ')';
            } else {
                $statusJB = 'BROKEN PROMISE';
            }
        }

        return [
            $no++,
            $row->kode_ao, // Kolom Kode
            $row->no_nasabah, // Kolom No.Ang
            strtoupper($row->nama_nasabah), // Kolom Nama
            $row->kode_ao, // Kolom Kode AO
            strtoupper($row->nama_ao ?? '-'), // Kolom AO (Nama Petugas)
            $row->catatan ?? '-', // Kolom Ket
            $statusJB // Kolom Status / JB
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Atur Lebar Kolom Secara Otomatis atau Manual
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('C')->setWidth(15); // No Angsuran
        $sheet->getColumnDimension('D')->setWidth(25); // Nama Nasabah
        $sheet->getColumnDimension('F')->setWidth(20); // Nama AO
        $sheet->getColumnDimension('G')->setWidth(30); // Catatan (Ket)
        $sheet->getColumnDimension('H')->setWidth(25); // Status

        return [
            // Baris Header: Bold, Center, Border
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center'],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9D9D9'] // Abu-abu muda agar profesional
                ]
            ],
            // Semua isi tabel: Wrap text agar catatan panjang tidak terpotong
            'A1:H1000' => [
                'alignment' => ['vertical' => 'center', 'wrapText' => true],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }
}