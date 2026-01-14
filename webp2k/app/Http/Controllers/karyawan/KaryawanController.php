<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
   public function index(Request $request)
    {
        // 1. Data Dummy (Nanti ganti dengan query Database)
        $karyawan = [
            ['kode' => 'PG.803', 'nama' => 'WAHYU'],
        ];

        // 2. Jika dipanggil lewat Fetch/AJAX (SPA)
        if ($request->ajax()) {
            return view('admin.partials.karyawan_table', compact('karyawan'))->render();
        }

        // 3. Jika dibuka langsung/refresh browser (Full Page)
        return view('admin.datakaryawan', compact('karyawan'));
    }
}
