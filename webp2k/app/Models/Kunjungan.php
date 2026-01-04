<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kunjungan extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_nasabah',
        'nama_nasabah',
        'keterangan_nasabah',
        'ada_di_lokasi',
        'catatan',
        'foto_kunjungan',
        'koordinat',
    ];
}