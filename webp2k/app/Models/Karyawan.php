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
        return $this->hasMany(DataKunjunganAdm::class, 'karyawan_id', 'id');
    }
}
