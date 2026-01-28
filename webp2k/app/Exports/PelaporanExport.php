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

    public function view(): View {
        // Ambil AO, dan ambil detail nasabahnya yang difilter berdasarkan tanggal
        $data_gabungan = Karyawan::with(['kunjungan' => function($query) {
            $query->whereBetween('tanggal', [$this->tglAwal, $this->tglAkhir]);
        }])
        ->whereHas('kunjungan', function($query) {
            $query->whereBetween('tanggal', [$this->tglAwal, $this->tglAkhir]);
        })->get();

        return view('admin.exports.pelaporan_excel', [
            'data_ao' => $data_gabungan,
            'tglAwal' => $this->tglAwal,
            'tglAkhir' => $this->tglAkhir
        ]);
    }
}