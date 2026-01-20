<?php

namespace App\Http\Controllers\karyawan;


use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function index()
    {
        return view('dashboard.dashboardadmin'); 
    }

    public function dataKaryawanContent()
    {
        $karyawan = Karyawan::all(); 
        return view('admin.partials.karyawan_table', compact('karyawan'));
    }
}
