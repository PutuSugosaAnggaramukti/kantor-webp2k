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
            ->leftJoin('nasabahs', 'kunjungans.no_nasabah', '=', 'nasabahs.no_angsuran')
            ->leftJoin('karyawans', 'kunjungans.kode_ao', '=', 'karyawans.kode_ao')
            // Join ke data_kunjungan_adms untuk mengambil kolom tanggal
            ->leftJoin('data_kunjungan_adms', 'kunjungans.no_nasabah', '=', 'data_kunjungan_adms.no_angsuran')
            ->select(
                'kunjungans.*', 
                'karyawans.nama as nama_ao', 
                'nasabahs.kode as kode_nasabah',
                'data_kunjungan_adms.tanggal as tgl_rencana' // Ambil kolom tanggal dari DB
            )
            ->orderBy('kunjungans.created_at', 'asc');

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
            'Tanggal Kunjungan',
            'Kode AO',
            'AO',
            'Ket',
            'Status / JB'
        ];
    }

   public function map($row): array
    {
        static $no = 1;

        // Logika Status JB (Tetap sesuai kode Mas)
        $statusJB = '-';
        if ($row->ada_di_lokasi == 'tidak') {
            $statusJB = 'TDK BERTEMU';
        } else {
            if ($row->nominal_janji_bayar > 0) {
                $tglJB = $row->tgl_janji_bayar ? \Carbon\Carbon::parse($row->tgl_janji_bayar)->format('d/m/y') : '';
                $statusJB = 'JANJI BAYAR (' . $tglJB . ')';
            } else {
                $statusJB = 'BROKEN PROMISE';
            }
        }

        return [
            $no++,
            $row->kode_nasabah ?? '-', 
            $row->no_nasabah, 
            strtoupper($row->nama_nasabah),
            // Menampilkan tanggal dari tabel data_kunjungan_adms
            $row->tgl_rencana ? \Carbon\Carbon::parse($row->tgl_rencana)->format('d/m/y') : '-', 
            $row->kode_ao,
            strtoupper($row->nama_ao ?? '-'),
            $row->catatan ?? '-',
            $statusJB
        ];
    }

   public function styles(Worksheet $sheet)
    {
        // Atur Lebar Kolom (Sudah benar semua)
        $sheet->getColumnDimension('A')->setWidth(5);   // No
        $sheet->getColumnDimension('B')->setWidth(12);  // Kode Nasabah
        $sheet->getColumnDimension('C')->setWidth(18);  // No Angsuran
        $sheet->getColumnDimension('D')->setWidth(25);  // Nama Nasabah
        $sheet->getColumnDimension('E')->setWidth(15);  // Tanggal
        $sheet->getColumnDimension('F')->setWidth(10);  // Kode AO
        $sheet->getColumnDimension('G')->setWidth(20);  // Nama AO
        $sheet->getColumnDimension('H')->setWidth(30);  // Catatan
        $sheet->getColumnDimension('I')->setWidth(25);  // Status

        // Ambil baris terakhir yang ada datanya secara dinamis
        $highestRow = $sheet->getHighestRow();
        $range = 'A1:I' . $highestRow;

        return [
            // Baris Header (Baris 1)
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center'],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9D9D9']
                ]
            ],
            // Semua isi tabel berdasarkan jumlah data yang ada
            $range => [
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