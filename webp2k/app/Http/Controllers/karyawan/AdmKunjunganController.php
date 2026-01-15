<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdmKunjunganController extends Controller
{
    public function index(){
        return view('admin.partials.kunjungan');
    }

    public function detail(){
        return view('admin.partials.detail_kunjungan');
    }
}
