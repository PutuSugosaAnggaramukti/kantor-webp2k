<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PelaporanController extends Controller
{
    public function index(){
        return view ('admin.partials.pelaporan');
    }
}
