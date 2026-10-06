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

    /**
     * Format kolom Excel mengikuti tampilan tab HB:
     * A: Kode | B: No. Ang | C: Nama | D: Alamat | E: Plafon Kredit |
     * F: Baki Debet | G: Kode AO | H: AO (diabaikan, diisi otomatis dari relasi)
     */
    public function model(array $row)
    {
        $noAngsuran = isset($row[1]) ? trim((string) $row[1]) : '';

        // Lewati baris kosong dan baris judul kolom
        if ($noAngsuran === '' || preg_match('/^no[.\s_-]*ang/i', $noAngsuran)) {
            return null;
        }

        $plafon     = $this->cleanNumber($row[4] ?? 0);
        $bakiDebet  = $this->cleanNumber($row[5] ?? 0) ?: $plafon;

        $kodeAo = isset($row[6]) ? trim((string) $row[6]) : '';
        if ($kodeAo === '' || $kodeAo === '-' || strtolower($kodeAo) === 'ao') {
            $kodeAo = null;
        }

        if ($kodeAo && !in_array($kodeAo, $this->kodeAoTidakDikenal, true)) {
            if (!\App\Models\Karyawan::where('kode_ao', $kodeAo)->exists()) {
                $this->kodeAoTidakDikenal[] = $kodeAo;
            }
        }

        $kode = isset($row[0]) ? trim((string) $row[0]) : '';

        return Nasabah::updateOrCreate(
            ['no_angsuran' => $noAngsuran],
            [
                'kode'            => ($kode === '' || strtolower($kode) === 'kode') ? '-' : $kode,
                'nasabah'         => trim((string) ($row[2] ?? '')) ?: '-',
                'alamat'          => trim((string) ($row[3] ?? '')) ?: '-',
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
