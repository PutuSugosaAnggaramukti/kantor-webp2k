<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IjinKunjungan extends Model
{
    // Tambahkan ini agar data bisa disimpan!
    protected $fillable = ['karyawan_id','kode_ao', 'tanggal', 'jenis_ijin', 'alasan','status'];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'kode_ao', 'kode_ao');
    }
}