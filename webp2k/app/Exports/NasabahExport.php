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
                'nasabahs.kode',              // A: Kode (PG.xxx)
                'nasabahs.no_angsuran',       // B: No. Angsuran
                'nasabahs.rekening_kredit',   // C: Rekening Kredit
                'nasabahs.bulan',             // D: Bulan (Sesuai screenshot Anda)
                
                // --- KOLOM AO MASTER (Data "89" tadi) ---
                DB::raw('IFNULL(nasabahs.kode_ao_nasabah, "-") as ao_master'), 
                
                // --- KOLOM AO P2K (Kunjungan) ---
                DB::raw('IFNULL(data_kunjungan_adms.kode_ao, "-") as petugas_ao_p2k'),
                
                DB::raw('IFNULL(karyawans.nama, "-") as nama_petugas'), 
                'nasabahs.nasabah', 
                'nasabahs.alamat', 
                'nasabahs.kol', 
                'nasabahs.sisa_pokok',
                'nasabahs.pokok_per_bulan',
                'nasabahs.bunga_per_bulan'
            )
            ->orderBy('nasabahs.nasabah', 'asc')
            ->get(); // Hapus groupBy agar lebih aman dan semua data muncul
    }

    public function headings(): array
    {
        return [
            'Kode',
            'No. Angsuran',
            'Rekening Kredit',
            'Bulan',
            'Kode AO Master', // Kolom untuk angka 89
            'Kode AO P2K',    // Kolom untuk C-007 dst
            'Nama Petugas (AO)', 
            'Nama Nasabah',
            'Alamat',
            'KOL',
            'Sisa Pokok',
            'Pokok/bln',
            'Bunga/bln'
        ];
    }
}