<?php

namespace App\Exports;

use App\Models\DataKunjunganAdm;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting; // Tambahkan ini
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;    // Tambahkan ini

class JadwalKunjunganExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
{
    public function collection()
    {
        // Mengambil semua data jadwal kunjungan
        return DataKunjunganAdm::all();
    }

    public function headings(): array
    {
        return [
            'No Ang',
            'Kode',
            'Kode AO',
            'Bulan',
            'Tgl Kunjungan',
            'Nama Nasabah',
            'Alamat',
            'Nominal',
            'Sisa Pokok',
            'KOL'
        ];
    }

    public function map($jadwal): array
    {
        return [
            // Tambahkan spasi di depan agar No Angsuran tidak jadi format E+ di Excel
            " " . $jadwal->no_angsuran, 
            $jadwal->kode_nasabah,
            $jadwal->kode_ao,
            $jadwal->bulan,
            $jadwal->tanggal ? \Carbon\Carbon::parse($jadwal->tanggal)->format('d/m/Y') : '-',
            $jadwal->nama_nasabah,
            $jadwal->alamat_nasabah,
            // Pastikan nominal dikirim sebagai angka agar bisa diformat di Excel
            (float) $jadwal->nominal,
            (float) $jadwal->sisa_pokok,
            $jadwal->kol,
        ];
    }

    // Mengatur format kolom (Nominal & Sisa Pokok jadi Akuntansi/Rupiah)
    public function columnFormats(): array
    {
        return [
            'H' => '#,##0', // Geser ke H karena ada tambahan kolom
            'I' => '#,##0', // Geser ke I
        ];
    }
}