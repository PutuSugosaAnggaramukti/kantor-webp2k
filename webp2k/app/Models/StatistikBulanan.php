<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatistikBulanan extends Model
{
    protected $table = 'statistik_bulanan';

    protected $fillable = [
        'bulan',
        'total_rencana',
        'sudah_dikunjungi',
        'belum_dikunjungi',
        'total_gagal',
    ];

    public function getBulanLabelAttribute()
    {
        return \Carbon\Carbon::createFromFormat('Y-m', $this->bulan)->translatedFormat('F Y');
    }
}