<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    // Pastikan nama tabel benar
    protected $table = 'karyawans'; 

    // Pastikan kolom-kolom ini ada
    protected $fillable = ['kode_ao', 'nama', 'username', 'password', 'status'];
}
