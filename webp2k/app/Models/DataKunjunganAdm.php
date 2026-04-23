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
        'alamat_nasabah',
        'nominal',      
        'sisa_pokok',   
        'kol',
        'bulan',
        'tanggal',
        'no_angsuran',
        'kode_nasabah',
        'karyawan_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];


    public function getNominalFormatAttribute()
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }

    public function getSisaFormatAttribute()
    {
        return 'Rp ' . number_format($this->sisa_pokok, 0, ',', '.');
    }


    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class, 'no_angsuran', 'no_angsuran');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }

    public function hasilKunjungan()
    {
        return $this->hasOne(HasilKunjungan::class, 'nama_nasabah', 'nama_nasabah');
    }
}