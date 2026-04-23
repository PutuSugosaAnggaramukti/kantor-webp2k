<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DetailKunjunganExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormatting
{
    protected $no_nasabah;

    // Kita minta no_nasabah saat memanggil class ini
    public function __construct($no_nasabah)
    {
        $this->no_nasabah = $no_nasabah;
    }

    public function collection()
    {
        return DB::table('kunjungans')
            ->leftJoin('nasabahs', 'kunjungans.no_nasabah', '=', 'nasabahs.no_angsuran')
            ->leftJoin('karyawans', 'kunjungans.kode_ao', '=', 'karyawans.kode_ao')
            ->select(
                'nasabahs.*', 
                'kunjungans.created_at as tgl_kunjungan',
                'kunjungans.catatan',
                'kunjungans.ada_di_lokasi',
                'kunjungans.nominal_janji_bayar',
                'kunjungans.tgl_janji_bayar',
                'kunjungans.kode_ao as k_ao',
                'karyawans.nama as nama_ao'
            )
            ->where('kunjungans.kode_ao', $this->no_nasabah) // Filter khusus nasabah ini
            ->orderBy('kunjungans.created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        // Sama persis dengan KunjunganExport Mas
        return [
            'kode', 'no.ang', 'rekening kredit', 'kode nasabah', 'nama', 'alamat', 
            'tanggal pinjam', 'tanggal jt', 'nominal', 'sisa pokok', 'pokok/bulan', 
            'bunga/bulan', 'tunggakan pokok', 'tunggakan bunga', 'denda', 
            'total tunggakan', 'agunan', 'ikatan', 'kode ao', 'nama ao', 
            'tanggal kunjungan', 'keterangan', 'status/jb'
        ];
    }

    public function map($row): array
    {
        // Logika Status JB sama persis
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
            $row->kode ?? '-',
            $row->no_angsuran,
            $row->rekening_kredit ?? '-',
            $row->kode_nasabah ?? '-',
            strtoupper($row->nasabah), 
            $row->alamat ?? '-',
            $row->tgl_pinjam ? \Carbon\Carbon::parse($row->tgl_pinjam)->format('d/m/Y') : '-',
            $row->tgl_jt ? \Carbon\Carbon::parse($row->tgl_jt)->format('d/m/Y') : '-',
            $row->nominal,
            $row->sisa_pokok,
            $row->pokok_per_bulan,
            $row->bunga_per_bulan,
            $row->tunggakan_pokok,
            $row->tunggakan_bunga,
            $row->denda,
            $row->bakidebet, 
            $row->kode_agunan ?? '-',
            $row->ikatan ?? '-',
            $row->k_ao,
            strtoupper($row->nama_ao ?? '-'),
            $row->tgl_kunjungan ? \Carbon\Carbon::parse($row->tgl_kunjungan)->format('d/m/Y H:i') : '-',
            $row->catatan ?? '-',
            $statusJB
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT, 
            'I' => '#,##0', 'J' => '#,##0', 'K' => '#,##0', 'L' => '#,##0',
            'M' => '#,##0', 'N' => '#,##0', 'O' => '#,##0', 'P' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        foreach (range('A', 'W') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $highestRow = $sheet->getHighestRow();
        $range = 'A1:W' . $highestRow;

        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center'],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9D9D9']
                ]
            ],
            'D' => ['alignment' => ['horizontal' => 'center']],
            $range => [
                'alignment' => ['vertical' => 'center'],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }
}