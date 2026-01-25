<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataKunjunganAdm extends Model
{
    use HasFactory;

    protected $table = 'data_kunjungan_adms';
    protected $fillable = [
        'kode_ao',
        'nama_nasabah',
        'kol',
        'bulan',
        'karyawan_id',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }

    public function hasilKunjungan()
    {
        return $this->hasOne(HasilKunjungan::class, 'nama_nasabah', 'nama_nasabah');
    }
}
