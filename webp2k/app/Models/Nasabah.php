<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nasabah extends Model
{
    use HasFactory;

    protected $primaryKey = 'no_angsuran';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode',
        'kode_ao_nasabah',
        'kode_agunan',
        'no_angsuran',
        'rekening_kredit',
        'kode_nasabah',
        'nasabah',
        'alamat',
        'ikatan',
        'tgl_pinjam',
        'tgl_jt',
        'nominal',
        'sisa_pokok',
        'pokok_per_bulan',
        'bunga_per_bulan',
        'tunggakan_pokok',
        'hari_pokok',
        'tunggakan_bunga',
        'hari_bunga',
        'denda',
        'bakidebet',
        'kol',
        'bulan'
    ];

   public function laporanSelesai()
    {
        // 'no_nasabah' adalah kolom di tabel kunjungans
        // 'no_angsuran' adalah kolom primary key di tabel nasabahs
        return $this->hasMany(\App\Models\Kunjungan::class, 'no_nasabah', 'no_angsuran');
    }

    // Relasi ke Karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'kode_ao', 'kode_ao');
    }

    // Relasi ke tabel kunjungan (DataKunjunganAdm)
   public function kunjungan()
    {
        // DI SINI MASALAHNYA: Pastikan foreign key-nya 'no_angsuran', BUKAN 'no_nasabah'
        return $this->hasMany(\App\Models\DataKunjunganAdm::class, 'no_angsuran', 'no_angsuran');
    }

    /**
     * ACCESSORS UNTUK TAMPILAN RUPIAH
     * Memudahkan pemanggilan di Blade: $nasabah->plafon_rupiah
     */

    public function getPlafonRupiahAttribute()
    {
        return 'Rp ' . number_format($this->plafon, 0, ',', '.');
    }

    public function getBakidebetRupiahAttribute()
    {
        return 'Rp ' . number_format($this->bakidebet, 0, ',', '.');
    }

    public function getTotalTunggakanRupiahAttribute()
    {
        return 'Rp ' . number_format($this->total_tunggakan, 0, ',', '.');
    }

    // Accessor lama (Opsional: agar view lama tidak error jika masih pakai nama 'nominal')
    public function getNominalRupiahAttribute()
    {
        return $this->getPlafonRupiahAttribute();
    }
}