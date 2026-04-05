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
        'no_angsuran',
        'rekening_kredit',
        'kode_nasabah',
        'nasabah',
        'alamat',
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

    // Relasi ke Karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'kode_ao', 'kode_ao');
    }

    // Relasi ke tabel kunjungan (DataKunjunganAdm)
    public function kunjungan()
    {
        /**
         * Penjelasan parameter:
         * 1. DataKunjunganAdm::class -> Model tujuan
         * 2. 'no_angsuran' -> Foreign Key di tabel kunjungan
         * 3. 'no_angsuran' -> Local Key di tabel nasabah
         */
        return $this->hasMany(DataKunjunganAdm::class, 'no_angsuran', 'no_angsuran');
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