<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class NasabahExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $tglAwal, $tglAkhir;

    public function __construct($tglAwal, $tglAkhir)
    {
        $this->tglAwal = $tglAwal;
        $this->tglAkhir = $tglAkhir;
    }

    public function collection()
    {
        return DB::table('nasabahs')
            ->leftJoin('data_kunjungan_adms', 'nasabahs.no_angsuran', '=', 'data_kunjungan_adms.no_angsuran')
            ->leftJoin('karyawans', 'data_kunjungan_adms.kode_ao', '=', 'karyawans.kode_ao')
            ->whereBetween('nasabahs.created_at', [$this->tglAwal . ' 00:00:00', $this->tglAkhir . ' 23:59:59'])
            ->select(
                'nasabahs.no_angsuran', 
                'nasabahs.nasabah', 
                'nasabahs.alamat', 
                'nasabahs.kol', 
                // Kolom Nama Petugas
                DB::raw('IFNULL(karyawans.nama, "-") as nama_petugas'), 
                // Kolom Kode AO
                DB::raw('IFNULL(data_kunjungan_adms.kode_ao, "-") as petugas_ao'),
                'nasabahs.bulan'
            )
            ->orderBy('nasabahs.nasabah', 'asc')
            ->groupBy(
                'nasabahs.no_angsuran', 
                'nasabahs.nasabah', 
                'nasabahs.alamat', 
                'nasabahs.kol', 
                'karyawans.nama', 
                'data_kunjungan_adms.kode_ao', 
                'nasabahs.bulan'
            )
            ->get();
    }

    public function headings(): array
    {
        return [
            'No. Angsuran',
            'Nama Nasabah',
            'Alamat',
            'KOL',
            'Nama Petugas (AO)', // Kolom baru
            'Kode AO',           // Kolom baru
            'Bulan'
        ];
    }
}