<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DokumenController extends Controller {
    
  public function dokumenContent() { 
    $user = Auth::guard('karyawan')->user();
    if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

    $myCode = strtoupper(trim($user->kode_ao));

    $dokumenQuery = \DB::table('kunjungans')
        ->leftJoin('nasabahs', 'kunjungans.nama_nasabah', '=', 'nasabahs.nasabah')
        ->where('kunjungans.kode_ao', 'LIKE', '%' . $myCode . '%')
        ->select(
            'kunjungans.*', 
            'nasabahs.kol as kol_master'
        )
        ->orderBy('kunjungans.created_at', 'desc');

    $dokumen = $dokumenQuery->paginate(10)->withQueryString()->through(function($item) {
        return (object)[
            'id'           => $item->id,
            'kode_ao'      => $item->kode_ao,
            'nama_nasabah' => $item->nama_nasabah,
            'kol'          => $item->kol ?: ($item->kol_master ?: '-'),
            'periode'      => $item->bulan ?? $item->created_at,
            'tgl_lapor'    => $item->created_at 
        ];
    });
    
    return view('kunjungan.partials.dokumen_content', compact('dokumen'));
}
}