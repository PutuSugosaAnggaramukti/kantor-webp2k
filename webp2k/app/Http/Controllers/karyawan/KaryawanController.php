<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
  public function index(Request $request)
    {
        $karyawan = [
            ['kode' => 'PG.803', 'nama' => 'WAHYU'],
        ];
        if ($request->ajax() || $request->is('*-content')) {
            return view('admin.partials.karyawan_table', compact('karyawan'))->render();
        }

        return view('admin.datakaryawan', compact('karyawan'));
    }
}
