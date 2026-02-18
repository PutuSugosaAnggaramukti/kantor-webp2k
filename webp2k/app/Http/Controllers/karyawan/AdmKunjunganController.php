<?php

namespace App\Http\Controllers\karyawan;

use App\Http\Controllers\Controller;
use App\Models\DataKunjunganAdm;
use App\Models\Kunjungan;
use App\Models\Karyawan;
use App\Exports\KunjunganExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Dashboard\DashboardAdminController;

class AdmKunjunganController extends Controller
{
    public function index(Request $request) 
    {
        $karyawans = Karyawan::all();
        $kunjungansGrouped = DataKunjunganAdm::with('karyawan')
            ->orderBy('kol', 'desc')
            ->get()
            ->groupBy('kode_ao');

        if ($request->ajax()) {
            return view('admin.partials.input_kunjungan', compact('karyawans', 'kunjungansGrouped'))->render();
        }

        try {
            $dashboard = new \App\Http\Controllers\Dashboard\DashboardAdminController();
            $data = $dashboard->getDashboardData();
        } catch (\Exception $e) {
            $data = [
                'karyawan_count' => \App\Models\Karyawan::count(),
                'title' => 'Input Jadwal Kunjungan'
            ];
        }

        $data['content'] = view('admin.partials.input_kunjungan', compact('karyawans', 'kunjungansGrouped'))->render();
        $data['page'] = 'adm-kunjungan';
        $data['title'] = 'Input Jadwal Kunjungan';

        return view('admin.datakaryawan', $data);

    }
    
    public function dataKunjunganContent(Request $request)
    {
        $karyawan = \App\Models\Karyawan::where('status', 'aktif')->get(); 
        $kunjunganGrouped = \DB::table('kunjungans')->get()->groupBy('kode_ao'); 

        if ($request->ajax()) {
            return view('admin.partials.kunjungan', compact('karyawan', 'kunjunganGrouped'))->render();
        }

        try {
            $dashboard = new \App\Http\Controllers\Dashboard\DashboardAdminController();
            $data = $dashboard->getDashboardData(); 
        } catch (\Exception $e) {
            $data = ['karyawan_count' => \App\Models\Karyawan::count()];
        }

        $data['title'] = 'Data Kunjungan';
        $data['page'] = 'kunjungan';
        $data['content'] = view('admin.partials.kunjungan', compact('karyawan', 'kunjunganGrouped'))->render();

        return view('admin.datakaryawan', $data);
    }

    public function detail($kode_ao)
    {
        try {
            $data_detail = \DB::table('kunjungans')
                ->leftJoin('nasabahs', 'kunjungans.no_nasabah', '=', 'nasabahs.no_angsuran')
                ->leftJoin('data_kunjungan_adms', function ($join) {
                    $join->on('kunjungans.nama_nasabah', '=', 'data_kunjungan_adms.nama_nasabah')
                        ->on('kunjungans.kode_ao', '=', 'data_kunjungan_adms.kode_ao');
                })
                ->where('kunjungans.kode_ao', $kode_ao)
                ->select(
                    'kunjungans.id',
                    'kunjungans.created_at',
                    'kunjungans.nama_nasabah',
                    'kunjungans.no_nasabah',
                    'nasabahs.alamat as alamat_master',
                    'data_kunjungan_adms.alamat_nasabah as alamat_rencana',
                    'data_kunjungan_adms.no_angsuran as no_rencana'
                )
                ->orderBy('kunjungans.created_at', 'desc')
                ->get();

            return view('admin.partials.detail_kunjungan', compact('data_detail', 'kode_ao'));
        } catch (\Exception $e) {
            return "<div style='color:red; padding:20px;'>Error: " . $e->getMessage() . "</div>";
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'karyawan_id'    => 'required|exists:karyawans,id',
            'nama_nasabah'   => 'required|string|max:255',
            'alamat_nasabah' => 'required',
            'kol'            => 'required',
            'bulan'          => 'required',
            'no_angsuran'    => 'required',
            'tanggal'        => 'required|date',
        ]);

        $karyawan = Karyawan::find($request->karyawan_id);

        DataKunjunganAdm::create([
            'karyawan_id'    => $request->karyawan_id,
            'nama_nasabah'   => $request->nama_nasabah,
            'alamat_nasabah' => $request->alamat_nasabah,
            'kol'            => $request->kol,
            'bulan'          => $request->bulan,
            'no_angsuran'    => $request->no_angsuran,
            'tanggal'        => $request->tanggal,
            'kode_ao'        => $karyawan->kode_ao ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal kunjungan berhasil ditambahkan!'
        ]);
    }

    public function rekapKunjungan()
    {
        $rekap = Karyawan::withCount(['kunjungan as jumlah_kunjungan'])->get();
        return view('admin.rekap_kunjungan_content', compact('rekap'));
    }

    public function exportExcel(Request $request)
    {
        $kode_ao = $request->query('kode_ao');
        return Excel::download(new KunjunganExport($kode_ao), 'rekap_kunjungan_' . date('Y-m-d') . '.xlsx');
    }
}