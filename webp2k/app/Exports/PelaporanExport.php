<?php

namespace App\Exports;

use App\Models\Karyawan;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PelaporanExport implements FromView, ShouldAutoSize
{
    protected $tglAwal, $tglAkhir;

    public function __construct($tglAwal, $tglAkhir) {
        $this->tglAwal = $tglAwal;
        $this->tglAkhir = $tglAkhir;
    }

    public function view(): View 
    {
        // Kita langsung query ke tabel kunjungan agar datanya "Flat"
        $data_kunjungan = \App\Models\DataKunjunganAdm::with('karyawan')
            ->whereBetween('tanggal', [$this->tglAwal, $this->tglAkhir])
            ->orderBy('tanggal', 'desc')
            ->orderBy('kode_ao', 'asc')
            ->get();

        return view('admin.exports.pelaporan_excel', [
            'data_ao' => $data_kunjungan,
            'tglAwal' => $this->tglAwal,
            'tglAkhir' => $this->tglAkhir
        ]);
    }
}