<?php

namespace App\Imports;

use App\Models\Nasabah;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class NasabahImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Berdasarkan foto Excel kamu, kolomnya adalah "No Angsuran"
        // Laravel Excel mengubahnya menjadi "no_angsuran" (lowercase + underscore)
        $rawNoAng = $row['no_angsuran'] ?? $row['no_ang'] ?? null;

        if (empty($rawNoAng)) {
            return null;
        }

        return Nasabah::updateOrCreate(
            [
                'no_angsuran' => (string) round($rawNoAng)
            ], 
            [
                'nasabah'       => $row['nama_nasabah'] ?? $row['nama'] ?? '-',     
                'alamat'        => $row['alamat'] ?? '-',   
                'kol'           => (int) ($row['kol'] ?? 1),      
                'nominal'       => isset($row['nominal']) ? (float) $row['nominal'] : 0,                                
                'sisa_pokok'    => isset($row['sisa_pokok']) ? (float) $row['sisa_pokok'] : 0,                
                'bulan'         => date('Y-m'),      
                'kode'          => '-',               
                'sudah_kunjung' => 0,
                'kode_ao'       => $row['kode_ao'] ?? '-', 
                'nama_ao'       => '-'
            ]
        );
    }
}