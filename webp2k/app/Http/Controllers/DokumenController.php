<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HasilKunjungan; 
use Illuminate\Support\Facades\Auth;

class DokumenController extends Controller {
    
    public function dokumenContent() { 
        // 1. Ambil ID AO yang login
        $karyawanId = Auth::guard('karyawan')->id();

        // 2. Ambil data hasil kunjungan yang nasabahnya milik AO ini
        // Kita gunakan relasi 'dataKunjungan' yang merujuk ke tabel master nasabah
        $dokumen = HasilKunjungan::whereHas('dataKunjungan', function($query) use ($karyawanId) {
            $query->where('karyawan_id', $karyawanId);
        })
        ->with('dataKunjungan') // Eager load data nasabahnya
        ->latest()
        ->get();
        
        return view('kunjungan.partials.dokumen_content', compact('dokumen'));
    }
}