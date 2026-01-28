<?php

namespace App\Exports;

use App\Models\DataKunjunganAdm;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KunjunganExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * Ambil semua data kunjungan beserta relasi karyawan
    */
    public function collection()
    {
        // 'karyawan' adalah nama fungsi relasi di Model Kunjungan kamu
        return DataKunjunganAdm::with('karyawan')->get();
    }

    /**
    * Tentukan Judul Kolom di Excel
    */
    public function headings(): array
    {
        return [
            'No',
            'Tanggal Kunjungan',
            'Kode AO',
            'Nama AO (Karyawan)',
            'No Angsuran',
            'Nama Nasabah',
            'Alamat',
            'KOL'
        ];
    }

    /**
    * Petakan data dari Database ke Kolom Excel
    */
    public function map($kunjungan): array
    {
        static $no = 1;
        return [
            $no++,
            $kunjungan->tanggal,
            $kunjungan->kode_ao,
            $kunjungan->karyawan->nama ?? 'N/A',
            $kunjungan->no_angsuran,
            $kunjungan->nama_nasabah,
            $kunjungan->alamat_nasabah, 
            $kunjungan->kol,
        ];
    }
    
}