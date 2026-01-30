<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nasabah extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'no_angsuran',
        'nasabah',
        'alamat',
        'nominal',
        'sisa_pokok',
        'kol',
        'bulan',
        'kode_ao',
        'nama_ao',
        'sudah_kunjung'
    ];

    public function getNominalRupiahAttribute()
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }

    public function getSisaRupiahAttribute()
    {
        return 'Rp ' . number_format($this->sisa_pokok, 0, ',', '.');
    }
}