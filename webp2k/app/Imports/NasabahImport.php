<?php

namespace App\Imports;

use App\Models\Nasabah;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class NasabahImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Nasabah([

            'no_angsuran'   => (string) round($row['no_ang']), 
            'nasabah'       => $row['nama'],     
            'alamat'        => $row['alamat'],   
            'kol'           => (int) $row['kol'],      
            
            'nominal'       => isset($row['nominal']) ? (float) $row['nominal'] : 0,                
            'sisa_pokok'    => isset($row['sisa_pokok']) ? (float) $row['sisa_pokok'] : 0,               
            
            'bulan'         => date('Y-m'),      
            'kode'          => '-',               
            'sudah_kunjung' => 0,
            'kode_ao'       => '-', 
            'nama_ao'       => '-'
        ]);
    }
}