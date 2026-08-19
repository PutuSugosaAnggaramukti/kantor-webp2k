<?php

namespace App\Exports;

use App\Models\StatistikBulanan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StatistikBulananExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $bulan;

    public function __construct($bulan = null)
    {
        $this->bulan = $bulan;
    }

    public function collection()
    {
        $query = StatistikBulanan::orderBy('bulan', 'desc');
        if ($this->bulan) {
            $query->where('bulan', $this->bulan);
        }
        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Bulan',
            'Total Rencana',
            'Sudah Dikunjungi',
            'Belum Dikunjungi',
            'Total Gagal Kunjungan',
            'Persentase Realisasi (%)',
        ];
    }

    public function map($stat): array
    {
        $persen = $stat->total_rencana > 0
            ? round(($stat->sudah_dikunjungi / $stat->total_rencana) * 100, 2)
            : 0;

        return [
            \Carbon\Carbon::createFromFormat('Y-m', $stat->bulan)->translatedFormat('F Y'),
            $stat->total_rencana,
            $stat->sudah_dikunjungi,
            $stat->belum_dikunjungi,
            $stat->total_gagal,
            $persen,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center'],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9D9D9']
                ]
            ],
            'A1:F' . $highestRow => [
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
