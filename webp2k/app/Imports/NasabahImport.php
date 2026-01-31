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
            'no_angsuran' => $row['no_ang'], 
            'nasabah'     => $row['nama'],   
            'alamat'      => $row['alamat'],  
            'kol'         => $row['kol'],     
            'bulan'       => date('Y-m'),    
            'nominal'     => 0,              
            'sisa_pokok'  => 0,              
        ]);
    }
}