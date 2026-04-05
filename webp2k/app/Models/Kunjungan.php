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
        'bukti_transfer', // TAMBAHKAN INI agar bisa disimpan
        'koordinat',
        'tgl_janji_bayar',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
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