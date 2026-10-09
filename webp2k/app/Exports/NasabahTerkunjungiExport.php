<?php

namespace App\Exports;

use App\Models\Nasabah;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;

class NasabahTerkunjungiExport implements FromView, ShouldAutoSize
{
    public function view(): View
    {
        $nasabah_terkunjungi = Nasabah::whereHas('laporanSelesai', function($q) {
            $q->whereNotNull('no_nasabah');
        })
        ->with(['laporanSelesai' => function ($q) {
            // Urutkan laporan per nasabah: tanggal terlama di atas
            $q->orderBy('created_at', 'asc');
        }, 'laporanSelesai.karyawan'])
        ->orderBy('nasabah', 'asc')
        ->get();

        return view('admin.exports.nasabah_terkunjungi_excel', [
            'nasabah_terkunjungi' => $nasabah_terkunjungi,
        ]);
    }
}
