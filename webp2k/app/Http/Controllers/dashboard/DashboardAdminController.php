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
        // Statistik kotak atas: Menampilkan TOTAL SEMUA (Terjadwal + Selesai)
        $totalKunjungan = DataKunjunganAdm::count();

        // Grafik: Hanya menghitung AO yang SUDAH memiliki "Hasil Kunjungan"
        $performaAO = Karyawan::where('status', 'aktif') 
            ->withCount(['kunjungan' => function ($query) {
                // Gunakan relasi has() karena tabel data_kunjungan_adms tidak punya kolom foto
                $query->has('hasilKunjungan');
            }]) 
            ->get();

        $labels = $performaAO->pluck('nama'); 
        $counts = $performaAO->pluck('kunjungan_count'); 

        return view('dashboard.dashboardadmin', compact('totalKunjungan', 'labels', 'counts'));
    }
}