<?php

namespace App\Imports;

use App\Models\Nasabah;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class NasabahHBImport implements ToModel, WithStartRow, WithCustomCsvSettings
{
    public function model(array $row)
    {
        // 1. Validasi: Lewati jika No Angsuran kosong
        if (empty($row[0])) {
            return null;
        }

        // 2. Bersihkan No Angsuran (kadangkala ada spasi tersembunyi)
        $noAngsuran = trim($row[0]);

        // 3. Gunakan updateOrCreate agar data sinkron
        return Nasabah::updateOrCreate(
            ['no_angsuran' => $noAngsuran], 
            [
                'nasabah'       => $row[1] ?? '-',
                'alamat'        => $row[2] ?? '-',
                // Pastikan nominal terbaca sebagai angka bersih
                'nominal'       => isset($row[3]) ? (float)$row[3] : 0,
                'sisa_pokok'    => isset($row[3]) ? (float)$row[3] : 0,
                'kol'           => '5', // String '5' agar konsisten dengan filter in_array kita sebelumnya
                'bulan'         => date('Y-m'),
                'kode'          => '-',
                'sudah_kunjung' => 0,
                'kode_ao'       => 'HB',
                'nama_ao'       => 'Hapus Buku',
            ]
        );
    }

    public function startRow(): int
    {
        return 2; // Lewati header
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter'        => ",",
            'input_encoding'   => 'UTF-8',
            'enclosure'        => '"', 
        ];
    }
}