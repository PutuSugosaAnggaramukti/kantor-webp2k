<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilKunjungan extends Model
{
    use HasFactory;
    protected $table = 'kunjungans'; 
    protected $guarded = [];

    public function dataKunjungan()
    {
        return $this->belongsTo(DataKunjunganAdm::class, 'nama_nasabah', 'nama_nasabah');
    }
}