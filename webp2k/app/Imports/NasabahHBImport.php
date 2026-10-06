<?php

namespace App\Imports;

use App\Models\Nasabah;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class NasabahHBImport implements ToModel, WithStartRow, WithCustomCsvSettings
{
    /**
     * Kode AO dari file yang tidak ditemukan di tabel karyawans.
     * Dipakai controller untuk menampilkan peringatan setelah import.
     */
    public array $kodeAoTidakDikenal = [];

    /** Jumlah baris yang otomatis digeser karena file punya kolom ID di paling kiri */
    public static int $barisGeser = 0;

    /**
     * Format kolom Excel mengikuti tampilan tab HB:
     * A: Kode | B: No. Ang | C: Nama | D: Alamat | E: Plafon Kredit |
     * F: Baki Debet | G: Kode AO | H: AO (diabaikan, diisi otomatis dari relasi)
     *
     * Jika file punya kolom tambahan (ID baris) di paling kiri, semua kolom
     * geser 1 ke kanan dan otomatis dikoreksi oleh indexDasar().
     */
    public function model(array $row)
    {
        $c = $this->indexDasar($row); // 0 = normal, 1 = ada kolom ID di depan

        $noAngsuran = trim((string) ($row[$c + 1] ?? ''));

        // Lewati baris kosong dan baris judul kolom
        if ($noAngsuran === ''
            || preg_match('/^no[.\s_-]*ang/i', $noAngsuran)
            || preg_match('/^(kode|nama|alamat|plafon|baki|ao)\b/i', $noAngsuran)) {
            return null;
        }

        $kode      = trim((string) ($row[$c] ?? ''));
        $nama      = trim((string) ($row[$c + 2] ?? ''));
        $alamat    = trim((string) ($row[$c + 3] ?? ''));
        $plafon    = $this->cleanNumber($row[$c + 4] ?? 0);
        $bakiDebet = $this->cleanNumber($row[$c + 5] ?? 0) ?: $plafon;
        $kodeAo    = trim((string) ($row[$c + 6] ?? ''));

        // Nama & Alamat menyatu dalam satu sel ("NAMA/ALAMAT") sementara
        // kolom Alamat kosong -> pisahkan berdasarkan tanda "/"
        if (($alamat === '' || $alamat === '-' || $alamat === '0') && strpos($nama, '/') !== false) {
            $bagian = explode('/', $nama, 2);
            if (trim($bagian[1]) !== '' && trim($bagian[1]) !== '0') {
                $nama   = trim($bagian[0]);
                $alamat = trim($bagian[1]);
            }
        }

        if ($kodeAo === '' || $kodeAo === '-' || strtolower($kodeAo) === 'ao') {
            $kodeAo = null;
        }

        if ($kodeAo && !in_array($kodeAo, $this->kodeAoTidakDikenal, true)) {
            if (!\App\Models\Karyawan::where('kode_ao', $kodeAo)->exists()) {
                $this->kodeAoTidakDikenal[] = $kodeAo;
            }
        }

        return Nasabah::updateOrCreate(
            ['no_angsuran' => $noAngsuran],
            [
                'kode'            => ($kode === '' || strtolower($kode) === 'kode') ? '-' : $kode,
                'nasabah'         => $nama ?: '-',
                'alamat'          => $alamat ?: '-',
                'nominal'         => $plafon,
                'sisa_pokok'      => $bakiDebet,
                'bakidebet'       => $bakiDebet,
                'kode_ao_nasabah' => $kodeAo,
                'is_hb'           => 1,
                'kol'             => '5',
                'bulan'           => date('Y-m'),
            ]
        );
    }

    /**
     * Deteksi format baris:
     * - Normal : [Kode, No.Ang, Nama, Alamat, ...]  -> index 0
     * - Ber-ID : [ID, Kode, No.Ang, Nama, Alamat, ...] -> index 1
     * Pembeda: kolom B (No.Ang) berisi huruf / kosong dan kolom C angka >= 3 digit.
     */
    private function indexDasar(array $row): int
    {
        $kolomB = trim((string) ($row[1] ?? ''));
        $kolomC = trim((string) ($row[2] ?? ''));

        if (($kolomB === '' || preg_match('/[A-Za-z]/', $kolomB)) && preg_match('/^\d{3,}$/', $kolomC)) {
            self::$barisGeser++;
            return 1;
        }

        return 0;
    }

    public function startRow(): int
    {
        return 2; // Lewati baris judul
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter'      => ",",
            'input_encoding' => 'UTF-8',
            'enclosure'      => '"',
        ];
    }

    /**
     * Angka bersih: mendukung format "Rp 15.000.000", "15,000,000", "15000000"
     */
    private function cleanNumber($value): float
    {
        if ($value === null || $value === '') return 0;
        if (is_numeric($value)) return (float) $value;

        $clean = str_replace([' ', 'Rp', 'IDR', 'rp'], '', (string) $value);

        if (strpos($clean, ',') !== false && strpos($clean, '.') !== false) {
            if (strrpos($clean, ',') > strrpos($clean, '.')) {
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            } else {
                $clean = str_replace(',', '', $clean);
            }
        } elseif (strpos($clean, ',') !== false) {
            // 1,000,000 (US) atau 15,5 (desimal Indo)
            $clean = (substr_count($clean, ',') > 1) ? str_replace(',', '', $clean) : str_replace(',', '.', $clean);
        } elseif (strpos($clean, '.') !== false && substr_count($clean, '.') > 1) {
            // 15.000.000 (Indo) -> 15000000
            $clean = str_replace('.', '', $clean);
        }

        return is_numeric($clean) ? (float) $clean : 0;
    }
}
