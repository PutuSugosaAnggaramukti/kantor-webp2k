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
    // Ambil Karyawan yang punya kunjungan di tabel administrasi (data_kunjungan_adms)
    $data_gabungan = Karyawan::whereHas('kunjungan', function($query) {
            $query->whereBetween('tanggal', [$this->tglAwal, $this->tglAkhir]);
        })
        ->with(['kunjungan' => function($query) {
            $query->whereBetween('tanggal', [$this->tglAwal, $this->tglAkhir])
                  ->orderBy('tanggal', 'desc');
        }])
        ->get();

    return view('admin.exports.pelaporan_excel', [
        'data_ao' => $data_gabungan,
        'tglAwal' => $this->tglAwal,
        'tglAkhir' => $this->tglAkhir
    ]);
}
}