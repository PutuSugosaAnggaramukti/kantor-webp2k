<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

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

    public function kunjungan()
    {
        // Kita hubungkan 'id' di tabel karyawans 
        // ke kolom 'karyawan_id' di tabel data_kunjungan_adms
        return $this->hasMany(Kunjungan::class, 'karyawan_id', 'id');
    }
}
