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
    protected $bulan;
    protected $tahun;

    // 1. Update Constructor untuk menerima bulan dan tahun
    public function __construct($kode_ao = null, $bulan = null, $tahun = null)
    {
        $this->kode_ao = $kode_ao;
        $this->bulan = $bulan ?: date('m'); // Default ke bulan sekarang jika kosong
        $this->tahun = $tahun ?: date('Y'); // Default ke tahun sekarang jika kosong
    }

    public function collection()
    {
        $query = DB::table('data_kunjungan_adms')
            // Join ke nasabahs untuk data profil
            ->leftJoin('nasabahs', 'data_kunjungan_adms.no_angsuran', '=', 'nasabahs.no_angsuran')
            // Join ke karyawans untuk nama AO
            ->leftJoin('karyawans', 'data_kunjungan_adms.kode_ao', '=', 'karyawans.kode_ao')
            // Join ke tabel kunjungans untuk mengambil hasil inputan lapangan
            ->leftJoin('kunjungans', 'data_kunjungan_adms.no_angsuran', '=', 'kunjungans.no_nasabah')
            ->select(
                'nasabahs.*',
                'data_kunjungan_adms.tanggal as tgl_rencana',
                'data_kunjungan_adms.kode_ao as k_ao',
                'karyawans.nama as nama_ao',
                'data_kunjungan_adms.nominal as nominal_pinjam',
                'data_kunjungan_adms.sisa_pokok as sisa_pokok_adms',
                // Data hasil inputan dari tabel kunjungans
                'kunjungans.created_at as tgl_input_riil',
                'kunjungans.catatan',
                'kunjungans.ada_di_lokasi',
                'kunjungans.nominal_janji_bayar',
                'kunjungans.tgl_janji_bayar'
            )
            // Filter berdasarkan tanggal di jadwal (data_kunjungan_adms)
            ->whereMonth('data_kunjungan_adms.tanggal', $this->bulan)
            ->whereYear('data_kunjungan_adms.tanggal', $this->tahun)
            ->orderBy('data_kunjungan_adms.tanggal', 'asc');

        if (!empty($this->kode_ao)) {
            $query->where('data_kunjungan_adms.kode_ao', 'LIKE', '%' . $this->kode_ao . '%');
        }

        return $query->get();
    }

    public function headings(): array
    {
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
        // Logika Status Kunjungan/Janji Bayar
        $statusJB = 'BELUM DIKUNJUNGI'; // Default jika data di tabel kunjungans kosong
        
        if ($row->ada_di_lokasi) {
            if ($row->ada_di_lokasi == 'tidak') {
                $statusJB = 'TDK BERTEMU';
            } else {
                if (($row->nominal_janji_bayar ?? 0) > 0) {
                    $tglJB = $row->tgl_janji_bayar ? \Carbon\Carbon::parse($row->tgl_janji_bayar)->format('d/m/y') : '';
                    $statusJB = 'JANJI BAYAR (' . $tglJB . ')';
                } else {
                    $statusJB = 'BROKEN PROMISE';
                }
            }
        }

        return [
            $row->kode ?? '-',
            $row->no_angsuran,
            $row->rekening_kredit ?? '-',
            $row->kode_nasabah ?? '-',
            strtoupper($row->nasabah ?? '-'),
            $row->alamat ?? '-',
            $row->tgl_pinjam ? \Carbon\Carbon::parse($row->tgl_pinjam)->format('d/m/Y') : '-',
            $row->tgl_jt ? \Carbon\Carbon::parse($row->tgl_jt)->format('d/m/Y') : '-',
            (float)($row->nominal_pinjam ?? 0),
            (float)($row->sisa_pokok_adms ?? 0),
            (float)($row->pokok_per_bulan ?? 0),
            (float)($row->bunga_per_bulan ?? 0),
            (float)($row->tunggakan_pokok ?? 0),
            (float)($row->tunggakan_bunga ?? 0),
            (float)($row->denda ?? 0),
            (float)($row->bakidebet ?? 0),
            $row->kode_agunan ?? '-',
            $row->ikatan ?? '-',
            $row->k_ao,
            strtoupper($row->nama_ao ?? '-'),
            // Tampilkan tanggal rencana kunjungan jika belum ada input riil
            $row->tgl_input_riil 
                ? \Carbon\Carbon::parse($row->tgl_input_riil)->format('d/m/Y H:i') 
                : \Carbon\Carbon::parse($row->tgl_rencana)->format('d/m/Y') . ' (Jadwal)',
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