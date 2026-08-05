<?php

namespace App\Imports;

use App\Models\Nasabah;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;

class NasabahImport implements ToModel, WithMultipleSheets
{
    private $currentKol;
    private $labelBulan;

    public function __construct($kol = '1', $bulan = null)
    {
        $this->currentKol = $kol;
        $this->labelBulan = $bulan ?? date('F Y');
    }

   public function sheets(): array
    {
        return [
            0 => new NasabahImport('1', $this->labelBulan), // Sheet 1: Lancar
            1 => new NasabahImport('2', $this->labelBulan), // Sheet 2: Dalam Perhatian Khusus
            2 => new NasabahImport('3', $this->labelBulan), // Sheet 3: Kurang Lancar
            3 => new NasabahImport('4', $this->labelBulan), // Sheet 4: Diragukan
            4 => new NasabahImport('5', $this->labelBulan), // Sheet 5: Macet
        ];
    }

  public function model(array $row)
    {
        // 1. Ambil No Angsuran dari indeks 2 (Kolom C)
        $noAngsuran = isset($row[2]) ? trim($row[2]) : null;

        // VALIDASI: Lewati jika bukan baris data nasabah
        if (!$noAngsuran || $noAngsuran == 'No.Ang' || !is_numeric($noAngsuran)) {
            return null;
        }

        // 2. KOLOM "Kode AO" POSISINYA BERBEDA ANTAR SHEET:
        //    - Sheet 1-4 (Lancar, DPK, Kurang Lancar, Diragukan): kolom AK (indeks 36)
        //    - Sheet 5 (Macet): kolom AL (indeks 37)
        $kodeAoIdx = ($this->currentKol == '5') ? 37 : 36;

        $aoNasabahAsli = $this->detectKodeAo($row, $kodeAoIdx);

        // Gunakan logika serupa untuk Ikatan jika kolom AA (26) bergeser ke AB (27)
        $valAA = isset($row[26]) ? trim($row[26]) : '';
        $valAB = isset($row[27]) ? trim($row[27]) : '';
        $ikatanAsli = ($valAA !== '' && $valAA !== '0') ? $valAA : (($valAB !== '' && $valAB !== '0') ? $valAB : '-');

        $kodeAgunanAsli = isset($row[25]) ? trim($row[25]) : '-'; // Kolom Z

        return Nasabah::updateOrCreate(
            ['no_angsuran' => (string)$noAngsuran],
            [
                'kode'            => trim($row[1] ?? '-'),
                'rekening_kredit' => trim($row[3] ?? '-'),
                'kode_nasabah'    => trim($row[4] ?? '-'),
                'nasabah'         => trim($row[5] ?? '-'),
                'alamat'          => trim($row[6] ?? '-'),
                
                'kode_agunan'     => $kodeAgunanAsli, 
                'ikatan'          => $ikatanAsli,
                'kode_ao_nasabah' => $aoNasabahAsli, // Hasil deteksi dinamis

                'tgl_pinjam'      => $this->transformDate($row[8] ?? null), 
                'tgl_jt'          => $this->transformDate($row[9] ?? null), 
                
                'nominal'         => $this->cleanNumber($row[10] ?? 0),   
                'sisa_pokok'      => $this->cleanNumber($row[11] ?? 0),   
                'pokok_per_bulan' => $this->cleanNumber($row[12] ?? 0),   
                'bunga_per_bulan' => $this->cleanNumber($row[13] ?? 0),   
                'tunggakan_pokok' => $this->cleanNumber($row[14] ?? 0),   
                'hari_pokok'      => is_numeric($row[15] ?? null) ? (int)$row[15] : 0, 
                'tunggakan_bunga' => $this->cleanNumber($row[16] ?? 0), 
                'hari_bunga'      => is_numeric($row[17] ?? null) ? (int)$row[17] : 0,
                'denda'           => $this->cleanNumber($row[18] ?? 0),
                'bakidebet'       => $this->cleanNumber($row[19] ?? 0),   
                
                'kol'             => $this->currentKol,
                'bulan'           => $this->labelBulan,
            ]
        );
    }

    private function detectKodeAo($row, $primaryIdx)
    {
        // Kumpulkan kandidat dari kolom di sekitar posisi "Kode AO"
        $kandidat = [];
        $start = max(34, $primaryIdx - 1);
        $end   = min(38, $primaryIdx + 1);
        for ($i = $start; $i <= $end; $i++) {
            if (!isset($row[$i])) continue;
            $v = strtoupper(trim((string)$row[$i]));
            if ($v === '' || $v === '0' || $v === '-' || $v === 'NONE' || $v === 'NULL') continue;
            // Hanya terima nilai yang menyerupai kode AO: C-009, AO-022, FO-001, dst
            if (preg_match('/^[A-Z]{1,4}-\d+$/', $v)) {
                $kandidat[] = $v;
            }
        }
        if (empty($kandidat)) return '-';

        // Prioritas 1: kode C-xxx (cocok dengan kode login AO)
        foreach ($kandidat as $v) {
            if (preg_match('/^C-\d+$/', $v)) {
                return $v;
            }
        }

        // Prioritas 2: kode lain (AO-xxx, dst) yang valid
        return $kandidat[0];
    }

    private function cleanNumber($value) {
        if (empty($value)) return 0;
        if (is_numeric($value)) return (float) $value;

        // 1. Hapus spasi dan simbol mata uang jika ada
        $clean = str_replace([' ', 'Rp', 'IDR'], '', $value);
        
        // 2. Logika Pembersihan:
        // Jika formatnya 1.000.000,00 (standar Indo) -> ubah ke 1000000.00
        // Jika formatnya 1,000,000.00 (standar US) -> ubah ke 1000000.00
        
        // Cek jika ada koma DAN titik
        if (strpos($clean, ',') !== false && strpos($clean, '.') !== false) {
            if (strrpos($clean, ',') > strrpos($clean, '.')) {
                // Format Indo: 1.000,00 -> hapus titik, ubah koma jadi titik
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            } else {
                // Format US: 1,000.00 -> hapus koma
                $clean = str_replace(',', '', $clean);
            }
        } else {
            // Jika hanya ada koma, asumsikan itu adalah pemisah ribuan ATAU desimal
            // Untuk amannya di Excel Indonesia, biasanya koma adalah desimal.
            // Tapi jika setelah koma ada 3 digit, itu biasanya ribuan.
            $parts = explode(',', $clean);
            if (count($parts) > 1 && strlen(end($parts)) === 3) {
                $clean = str_replace(',', '', $clean);
            } else {
                $clean = str_replace(',', '.', $clean);
            }
        }

        return (float) filter_var($clean, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    private function transformDate($value) {
        if (empty($value)) return null;
        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            $dateStr = str_replace('/', '-', trim($value));
            return Carbon::parse($dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}