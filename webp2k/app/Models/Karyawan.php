<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Karyawan extends Authenticatable
{
    use Notifiable;
    
    protected $table = 'karyawans';
    protected $fillable = ['kode_ao', 'nama', 'username', 'password', 'status', 'avatar'];
    protected $hidden = ['password'];

    public function getRoleAttribute()
    {
        return 'user'; 
    }

    /**
     * RELASI RENCANA (ADM)
     * Menghitung target yang di-input Admin di tabel data_kunjungan_adms
     */
    public function kunjungan()
    {
        return $this->hasMany(DataKunjunganAdm::class, 'karyawan_id', 'id');
    }

    /**
     * RELASI REALISASI (AO)
     * Menghitung kunjungan yang sudah dilakukan AO di tabel kunjungans
     * Kita hubungkan via 'kode_ao' karena biasanya di tabel realisasi 
     * AO diidentifikasi melalui kode mereka.
     */
    public function realisasiKunjungan()
    {
        return $this->hasMany(Kunjungan::class, 'kode_ao', 'kode_ao');
    }

    public function ijinKunjungan()
    {
        return $this->hasMany(IjinKunjungan::class, 'karyawan_id', 'id');
    }
}