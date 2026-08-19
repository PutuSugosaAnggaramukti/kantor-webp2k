<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StatistikBulananDetailExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $bulan;

    public function __construct($bulan)
    {
        $this->bulan = $bulan;
    }

    public function collection()
    {
        [$tahun, $bulanAngka] = array_pad(explode('-', $this->bulan), 2, null);
        $tahun = (int) $tahun;
        $bulanAngka = (int) $bulanAngka;

        $rows = collect();

        // 1. Rencana (jadwal admin pada bulan tsb)
        $rencana = DB::table('data_kunjungan_adms')
            ->join('karyawans', 'data_kunjungan_adms.kode_ao', '=', 'karyawans.kode_ao')
            ->where('data_kunjungan_adms.bulan', $this->bulan)
            ->select(
                'data_kunjungan_adms.kode_ao',
                'karyawans.nama as nama_ao',
                'data_kunjungan_adms.nama_nasabah',
                'data_kunjungan_adms.tanggal'
            )
            ->get();

        foreach ($rencana as $r) {
            $rows->push((object)[
                'ao' => $r->kode_ao . ' - ' . $r->nama_ao,
                'nama_nasabah' => $r->nama_nasabah,
                'tanggal' => $r->tanggal,
                'status' => 'Rencana',
            ]);
        }

        // 2. Sudah Dikunjungi (realisasi pada bulan tsb)
        $sudah = DB::table('kunjungans')
            ->join('karyawans', 'kunjungans.kode_ao', '=', 'karyawans.kode_ao')
            ->whereYear('kunjungans.created_at', $tahun)
            ->whereMonth('kunjungans.created_at', $bulanAngka)
            ->select(
                'kunjungans.kode_ao',
                'karyawans.nama as nama_ao',
                'kunjungans.nama_nasabah',
                'kunjungans.created_at'
            )
            ->get();

        foreach ($sudah as $s) {
            $rows->push((object)[
                'ao' => $s->kode_ao . ' - ' . $s->nama_ao,
                'nama_nasabah' => $s->nama_nasabah,
                'tanggal' => $s->created_at,
                'status' => 'Sudah Dikunjungi',
            ]);
        }

        // 3. Belum Dikunjungi (rencana yang belum direalisasikan pada bulan tsb)
        $sudahKunjung = DB::table('kunjungans')
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulanAngka)
            ->pluck('nama_nasabah')
            ->toArray();

        $belum = DB::table('data_kunjungan_adms')
            ->join('karyawans', 'data_kunjungan_adms.kode_ao', '=', 'karyawans.kode_ao')
            ->where('data_kunjungan_adms.bulan', $this->bulan)
            ->whereNotIn('data_kunjungan_adms.nama_nasabah', $sudahKunjung)
            ->select(
                'data_kunjungan_adms.kode_ao',
                'karyawans.nama as nama_ao',
                'data_kunjungan_adms.nama_nasabah',
                'data_kunjungan_adms.tanggal'
            )
            ->get();

        foreach ($belum as $b) {
            $rows->push((object)[
                'ao' => $b->kode_ao . ' - ' . $b->nama_ao,
                'nama_nasabah' => $b->nama_nasabah,
                'tanggal' => $b->tanggal,
                'status' => 'Belum Dikunjungi',
            ]);
        }

        // 4. Gagal Kunjungan (ijin disetujui pada bulan tsb)
        $gagal = DB::table('ijin_kunjungans')
            ->leftJoin('karyawans as ao_lama', 'ijin_kunjungans.kode_ao', '=', 'ao_lama.kode_ao')
            ->whereIn('ijin_kunjungans.status', ['disetujui', 'DISETUJUI'])
            ->whereYear('ijin_kunjungans.tanggal', $tahun)
            ->whereMonth('ijin_kunjungans.tanggal', $bulanAngka)
            ->select(
                'ijin_kunjungans.kode_ao',
                'ao_lama.nama as nama_ao',
                'ijin_kunjungans.alasan',
                'ijin_kunjungans.tanggal'
            )
            ->get();

        foreach ($gagal as $g) {
            $rows->push((object)[
                'ao' => $g->kode_ao . ' - ' . ($g->nama_ao ?? 'N/A'),
                'nama_nasabah' => $g->alasan,
                'tanggal' => $g->tanggal,
                'status' => 'Gagal Kunjungan',
            ]);
        }

        return $rows->sortBy('tanggal')->values();
    }

    public function headings(): array
    {
        return [
            'AO (Kode - Nama)',
            'Nama Nasabah',
            'Tanggal',
            'Status',
        ];
    }

    public function map($row): array
    {
        $tanggal = $row->tanggal
            ? \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y')
            : '-';

        return [
            $row->ao,
            $row->nama_nasabah,
            $tanggal,
            $row->status,
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
            'A1:D' . $highestRow => [
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