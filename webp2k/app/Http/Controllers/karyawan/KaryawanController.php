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
        try {
            $request->validate([
                'kode_ao'  => 'required|unique:karyawans,kode_ao',
                'nama'     => 'required',
                'username' => 'required|unique:karyawans,username',
                'password' => 'required|min:6',
                'status'   => 'required'
            ]);

            Karyawan::create([
                'kode_ao'  => $request->kode_ao,
                'nama'     => $request->nama,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'status'   => $request->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data karyawan berhasil disimpan!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem.'
            ], 500);
        }
    }

   public function getList()
    {
        // Mengambil id dan nama karyawan terbaru
        return response()->json(Karyawan::select('id', 'nama')->get());
    }

    public function edit($id) {
        $karyawan = Karyawan::findOrFail($id);
        return response()->json($karyawan);
    }

   public function update(Request $request, $id) 
    {
        $karyawan = Karyawan::findOrFail($id);

        // 1. Siapkan data yang akan diupdate
        $data = [
            'kode_ao'  => $request->kode_ao,
            'nama'     => $request->nama,
            'username' => $request->username,
            'status'   => $request->status,
        ];

        // 2. Cek apakah user mengisi input password
        if ($request->filled('password')) {
            // Jika diisi, tambahkan password yang sudah di-hash ke array data
            $data['password'] = Hash::make($request->password);
        }

        // 3. Eksekusi update
        $karyawan->update($data);

        return redirect()->back()->with('success', 'Data karyawan berhasil diperbarui');
    }

    public function show($id)
    {
        // Mengambil data karyawan
        $karyawan = Karyawan::findOrFail($id);
        
        // Mengirimkan sebagai JSON ke JavaScript
        return response()->json($karyawan);
    }

}