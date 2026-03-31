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
        // 1. Validasi: Jika kolom No Angsuran kosong atau bukan angka, lewati baris ini
        if (!isset($row[0]) || !is_numeric($row[0])) {
            return null;
        }

        // 2. Gunakan updateOrCreate agar data yang sama tidak dobel
        return Nasabah::updateOrCreate(
            ['no_angsuran' => $row[0]], 
            [
                'nasabah'       => $row[1] ?? '-',
                'alamat'        => $row[2] ?? '-',
                'nominal'       => $row[3] ?? 0,
                'sisa_pokok'    => $row[3] ?? 0,
                'kol'           => 5,
                'bulan'         => date('Y-m'),
                'kode'          => '-',
                'sudah_kunjung' => 0,
                
                // TAMBAHKAN INI: Agar tidak error "Field doesn't have a default value"
                'kode_ao'       => 'HB',
                'nama_ao'       => 'Hapus Buku',
            ]
        );
    }

    // Melompati baris pertama (Header)
    public function startRow(): int
    {
        return 2;
    }

    // Pengaturan pembacaan CSV
    public function getCsvSettings(): array
    {
        return [
            'delimiter'        => ",",
            'input_encoding'   => 'UTF-8',
            'enclosure'        => '"', 
        ];
    }
}