<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdmDokumenController extends Controller
{
    public function dokumen(){
        return view('admin.partials.dokumen');
    }
}
