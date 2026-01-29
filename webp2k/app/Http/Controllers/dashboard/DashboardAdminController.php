<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\DataKunjunganAdm;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardAdminController extends Controller
{
       public function index()
    {
        // Total jadwal yang diinput admin
        $totalKunjungan = DataKunjunganAdm::count();

        // Hitung performa AO berdasarkan data yang SUDAH diupload di tabel 'kunjungans'
        $performaAO = Karyawan::where('status', 'aktif')
            ->get()
            ->map(function ($karyawan) {
                // Kita hitung berapa kali kode_ao ini muncul di tabel hasil kunjungan
                $karyawan->kunjungan_selesai = \DB::table('kunjungans')
                    ->where('kode_ao', $karyawan->kode_ao)
                    ->count();
                return $karyawan;
            });

        $labels = $performaAO->pluck('nama'); 
        $counts = $performaAO->pluck('kunjungan_selesai'); 

        return view('dashboard.dashboardadmin', compact('totalKunjungan', 'labels', 'counts'));
    }
}