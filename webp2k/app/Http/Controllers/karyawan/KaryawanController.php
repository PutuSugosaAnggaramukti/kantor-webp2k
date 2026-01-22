<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function index()
    {
        $karyawan = karyawan::all();
        return view('admin.datakaryawan', compact('karyawan'));
    }

    public function getContent(){
        $karyawan = karyawan::all();
        return view('admin.partials.karyawan_table', compact('karyawan'));

    }
}