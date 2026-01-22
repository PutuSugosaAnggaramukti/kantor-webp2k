<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

   public function store(Request $request)
    {
        // Pastikan validasi mengembalikan pesan yang jelas
        $request->validate([
            'kode_ao'  => 'required|unique:karyawans,kode_ao',
            'nama'     => 'required',
            'username' => 'required|unique:karyawans,username',
            'password' => 'required|min:6',
            'status'   => 'required'
        ]);

        // Proses Simpan
        Karyawan::create([
            'kode_ao'  => $request->kode_ao,
            'nama'     => $request->nama,
            'username' => $request->username,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'status'   => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data karyawan berhasil disimpan!'
        ]);
    }
}