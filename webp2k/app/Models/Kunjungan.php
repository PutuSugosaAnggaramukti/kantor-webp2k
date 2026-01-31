<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kunjungan extends Model
{
    use HasFactory;

   protected $table = 'data_kunjungan_adms';

    protected $fillable = [
        'kode_ao',
        'tanggal',
        'no_nasabah',
        'nama_nasabah',
        'keterangan_nasabah',
        'ada_di_lokasi',
        'catatan',
        'foto_kunjungan',
        'koordinat',
        'tgl_janji_bayar',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }
    

public function detailKunjungan()
{
    // Kita hubungkan data_kunjungan_adms ke tabel kunjungans
    // Gunakan 'no_nasabah' sebagai kunci penghubung
    return $this->hasOne(Kunjungan::class, 'no_nasabah', 'no_nasabah')
                ->from('kunjungans'); 
}
    public function hasilKunjungan()
    {
        // Jika di tabel ini ada foto, gunakan whereNotNull('foto_kunjungan')
        // Jika tidak ada foto, cukup kembalikan hasOne tanpa filter agar tidak error
        return $this->hasOne(Kunjungan::class, 'id', 'id');
    }
}