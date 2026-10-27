<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JanjiBayarHistory
{
    /**
     * Catat perubahan tanggal janji bayar. Tidak mencatat apa-apa jika tanggal tidak berubah.
     * Dipanggil SEBELUM kolom kunjungans di-update, dengan tanggal lama dari database.
     */
    public static function catat(int $kunjunganId, ?string $tglLama, ?string $tglBaruInput): void
    {
        $lama = self::normal($tglLama);
        $baru = self::normal($tglBaruInput);

        if ($lama === $baru) {
            return;
        }

        DB::table('kunjungan_janji_histories')->insert([
            'kunjungan_id' => $kunjunganId,
            'tgl_lama'     => $lama,
            'tgl_baru'     => $baru,
            'diubah_oleh'  => self::aktor(),
            'created_at'   => now(),
        ]);
    }

    /** Riwayat terbaru di atas, untuk ditampilkan di Detail Bukti. */
    public static function daftar(int $kunjunganId)
    {
        return DB::table('kunjungan_janji_histories')
            ->where('kunjungan_id', $kunjunganId)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    private static function normal(?string $tgl): ?string
    {
        if ($tgl === null || trim($tgl) === '') {
            return null;
        }

        return Carbon::parse($tgl)->format('Y-m-d');
    }

    private static function aktor(): string
    {
        $user = Auth::guard('karyawan')->user() ?? Auth::user();

        return $user->nama ?? $user->name ?? 'Tidak diketahui';
    }
}
