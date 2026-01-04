<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kunjungan; 

class DokumenController extends Controller {
    
    public function dokumenContent() { 
        // Hapus orderBy('tanggal_kunjungan') karena kolom tersebut tidak ada di database
        $dokumen = Kunjungan::all(); 
        
        return view('kunjungan.partials.dokumen_content', compact('dokumen'));
    }
}