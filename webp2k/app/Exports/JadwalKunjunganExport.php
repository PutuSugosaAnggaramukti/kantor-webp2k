<?php

namespace App\Exports;

use App\Models\DataKunjunganAdm;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles; // Tambahkan ini untuk border & warna
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Tambahkan ini agar kolom lebar otomatis
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JadwalKunjunganExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize, WithStyles
{
    protected $bulan;
    protected $tahun;

    public function __construct($bulan, $tahun)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function collection()
    {
       return DataKunjunganAdm::whereMonth('tanggal', $this->bulan)
                               ->whereYear('tanggal', $this->tahun)
                               ->get();
    }

    public function headings(): array
    {
        return [
            'no ang', 
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
            'tanggal kunjungan'
        ];
    }

   public function map($jadwal): array
    {
        // Mencari data di model Nasabah untuk mengisi kolom pendukung
        $nasabah = \App\Models\Nasabah::where('no_angsuran', $jadwal->no_angsuran)->first();

        // Hitung total tunggakan
        $totalTunggakan = ($nasabah->tunggakan_pokok ?? 0) + ($nasabah->tunggakan_bunga ?? 0) + ($nasabah->denda ?? 0);

        return [
            " " . $jadwal->no_angsuran,
            $jadwal->kode_nasabah ?? ($nasabah->kode_nasabah ?? '-'),
            strtoupper($jadwal->nama_nasabah),
            $jadwal->alamat_nasabah,
            $nasabah && $nasabah->tgl_pinjam ? \Carbon\Carbon::parse($nasabah->tgl_pinjam)->format('d/m/Y') : '-',
            $nasabah && $nasabah->tgl_jt ? \Carbon\Carbon::parse($nasabah->tgl_jt)->format('d/m/Y') : '-',
            (float) $jadwal->nominal,
            (float) $jadwal->sisa_pokok,
            (float) ($nasabah->pokok_per_bulan ?? 0),
            (float) ($nasabah->bunga_per_bulan ?? 0),
            (float) ($nasabah->tunggakan_pokok ?? 0),
            (float) ($nasabah->tunggakan_bunga ?? 0),
            (float) ($nasabah->denda ?? 0),
            (float) $totalTunggakan,
            $nasabah->kode_agunan ?? '-',
            $nasabah->ikatan ?? '-',
            $jadwal->kode_ao,
            
            $jadwal->tanggal ? \Carbon\Carbon::parse($jadwal->tanggal)->format('d/m/Y') : '-'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0', 
            'H' => '#,##0', 
            'I' => '#,##0', 
            'J' => '#,##0', 
            'K' => '#,##0', 
            'L' => '#,##0', 
            'M' => '#,##0', 
            'N' => '#,##0', 
        ];
    }

   public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        
        $range = 'A1:R' . $highestRow; 

        return [
            // Style Header
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center'],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9D9D9']
                ]
            ],
            // Style Body (Border & Alignment)
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