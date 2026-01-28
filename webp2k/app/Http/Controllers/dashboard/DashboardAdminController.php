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
        $totalKunjungan = DataKunjunganAdm::count();
        $performaAO = Karyawan::where('status', 'aktif') 
            ->withCount('kunjungan') 
            ->get();

        $labels = $performaAO->pluck('nama'); 
        $counts = $performaAO->pluck('kunjungan_count'); 

        return view('dashboard.dashboardadmin', compact('totalKunjungan', 'labels', 'counts'));
    }
}