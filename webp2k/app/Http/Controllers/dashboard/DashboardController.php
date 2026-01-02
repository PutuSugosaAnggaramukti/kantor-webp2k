<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Data ini nantinya bisa Anda ambil dari Database
        $total_kunjungan = 50;
        $sudah_dikunjungi = 33;

        // Data untuk Grafik Chart.js
        $labels = ['2025/10/15', '2025/10/16', '2025/10/17', '2025/10/18', '2025/10/19', '2025/10/20', '2025/10/21'];
        $nasabah_ada = [5, 6, 7, 8, 9, 10, 11];
        $nasabah_tidak_ada = [2, 3, 4, 5, 6, 7, 8];

        return view('dashboard.dashboard', compact(
            'total_kunjungan', 
            'sudah_dikunjungi', 
            'labels', 
            'nasabah_ada', 
            'nasabah_tidak_ada'
        ));
    }
}
