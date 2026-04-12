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
        $data_kunjungan = \App\Models\DataKunjunganAdm::with(['karyawan', 'nasabah'])
            ->whereBetween('tanggal', [$this->tglAwal, $this->tglAkhir])
            ->get();

        // Mengurutkan dengan beberapa kriteria (Multi-level Sorting)
        $sorted_data = $data_kunjungan->sort(function($a, $b) {
            // 1. Ambil status catatan untuk masing-masing
            $catatanA = \DB::table('kunjungans')
                ->where('no_nasabah', $a->no_angsuran)
                ->where('kode_ao', $a->kode_ao)
                ->whereNotNull('catatan')->where('catatan', '!=', '')->exists();

            $catatanB = \DB::table('kunjungans')
                ->where('no_nasabah', $b->no_angsuran)
                ->where('kode_ao', $b->kode_ao)
                ->whereNotNull('catatan')->where('catatan', '!=', '')->exists();

            // KRITERIA 1: Urutkan berdasarkan ada/tidaknya catatan (Descending)
            if ($catatanA != $catatanB) {
                return $catatanB <=> $catatanA;
            }

            // KRITERIA 2: Jika sama-sama ada catatan (atau sama-sama kosong), 
            // urutkan berdasarkan Nama Nasabah (Ascending) agar data seperti SUWARNO berkumpul
            return strcmp($a->nama_nasabah, $b->nama_nasabah);
        });

        return view('admin.exports.pelaporan_excel', [
            'data_ao' => $sorted_data,
            'tglAwal' => $this->tglAwal,
            'tglAkhir' => $this->tglAkhir
        ]);
    }
}