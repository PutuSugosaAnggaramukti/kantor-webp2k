<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kunjungan extends Model
{
    use HasFactory;

    protected $table = 'kunjungans';

    protected $fillable = [
        'kode_ao',
        'tanggal',
        'no_nasabah',
        'nama_nasabah',
        'alamat_nasabah',
        'keterangan_nasabah',
        'ada_di_lokasi',
        'catatan',
        'foto_kunjungan',
        'bukti_transfer', // TAMBAHKAN INI agar bisa disimpan
        'koordinat',
        'tgl_janji_bayar',
        'nominal_janji_bayar',
    ];

    public function karyawan()
    {
        // Pastikan ini merujuk ke Karyawan, dan kolom foreign key-nya 'kode_ao'
        return $this->belongsTo(Karyawan::class, 'kode_ao', 'kode_ao');
    }

    public function detailKunjungan()
    {
        // Tetap seperti ini jika memang relasinya ke tabel 'kunjungans'
        return $this->hasOne(Kunjungan::class, 'no_nasabah', 'no_nasabah')
                    ->from('kunjungans'); 
    }

    public function hasilKunjungan()
    {
        return $this->hasOne(Kunjungan::class, 'id', 'id');
    }
}