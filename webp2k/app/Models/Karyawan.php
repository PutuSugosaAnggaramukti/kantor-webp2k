<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $fillable = [
        'kode_ao', 
        'nama', 
        'username', 
        'password', 
        'status'
    ];
}
