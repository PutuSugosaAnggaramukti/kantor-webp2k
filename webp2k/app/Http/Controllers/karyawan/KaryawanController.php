<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function index()
    {
        // Ini hanya untuk memuat layout utama (Sidebar, Header, dsb)
        $karyawan = Karyawan::all(); 
        return view('dashboard.dashboardadmin', compact('karyawan'));
    }

    public function dataKaryawanContent()
    {
        $karyawan = Karyawan::all(); 
        // Pastikan view ini HANYA berisi potongan kode tabel yang Anda kirim tadi
        return view('karyawan.partials.karyawan_table', compact('karyawan'));
    }
}