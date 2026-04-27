<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting; 
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KunjunganExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormatting
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
            ->orderBy('kunjungans.created_at', 'asc');

        if (!empty($this->kode_ao)) {
            $query->where('kunjungans.kode_ao', 'LIKE', '%' . $this->kode_ao . '%');
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'kode', 
            'no.ang', 
            'rekening kredit', 
            'kode nasabah', 
            'nama', 
            'alamat', 
            'tanggal pinjam', 
            'tanggal jt', 
            'nominal', 
            'sisa pokok', 
            'pokok/bulan', 
            'bunga/bulan', 
            'tunggakan pokok', 
            'tunggakan bunga', 
            'denda', 
            'total tunggakan', 
            'agunan', 
            'ikatan', 
            'kode ao', 
            'nama ao', 
            'tanggal kunjungan', 
            'keterangan', 
            'status/jb'
        ];
    }

    public function map($row): array
    {
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

    /**
     * Mengatur format kolom agar menjadi format angka/nominal
     */
   public function columnFormats(): array
    {
        return [
            'D' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT, 
            'I' => '#,##0', // Nominal
            'J' => '#,##0', // Sisa Pokok
            'K' => '#,##0', // Pokok/Bulan
            'L' => '#,##0', // Bunga/Bulan
            'M' => '#,##0', // Tunggakan Pokok
            'N' => '#,##0', // Tunggakan Bunga
            'O' => '#,##0', // Denda
            'P' => '#,##0', // Total Tunggakan
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
            // Header tetap bold dan center
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center'],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9D9D9']
                ]
            ],
            // Merapikan Kolom D (Kode Nasabah) ke Tengah
            'D' => [
                'alignment' => ['horizontal' => 'center']
            ],
            // Semua sel agar rapi secara vertikal dan memiliki border
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