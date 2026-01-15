<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NasabahController extends Controller
{
    public function index(){
        return view('admin.partials.nasabah_table');
    }

    public function pengunjung(){
        return view('admin.partials.pengunjung_nasabah');
    }
}
