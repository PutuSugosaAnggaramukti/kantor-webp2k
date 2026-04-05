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
        // Indeks 2 adalah kolom 'No.Ang' di file Anda
        $noAngsuran = isset($row[2]) ? trim($row[2]) : null;

        // Lewati baris jika No.Angsuran bukan angka (header/footer sampah)
        if (!$noAngsuran || !is_numeric($noAngsuran)) {
            return null;
        }

        return Nasabah::updateOrCreate(
            ['no_angsuran' => (string)$noAngsuran],
            [
                'kode'             => trim($row[1] ?? '-'),      // Kolom B
                'rekening_kredit'  => trim($row[3] ?? '-'),      // Kolom D
                'kode_nasabah'     => trim($row[4] ?? '-'),      // Kolom E
                'nasabah'          => trim($row[5] ?? '-'),      // Kolom F
                'alamat'           => trim($row[6] ?? '-'),      // Kolom G
                'tgl_pinjam'       => $this->transformDate($row[8] ?? null), // Kolom I
                'tgl_jt'           => $this->transformDate($row[9] ?? null), // Kolom J
                
                'nominal'          => $this->cleanNumber($row[10] ?? 0),    // Kolom K (Plafon)
                'sisa_pokok'       => $this->cleanNumber($row[11] ?? 0),    // Kolom L (Bakidebet)
                'pokok_per_bulan'  => $this->cleanNumber($row[12] ?? 0),    // Kolom M
                'bunga_per_bulan'  => $this->cleanNumber($row[13] ?? 0),    // Kolom N
                'tunggakan_pokok'  => $this->cleanNumber($row[14] ?? 0),    // Kolom O
                'hari_pokok'       => is_numeric($row[15] ?? null) ? (int)$row[15] : 0, // Kolom P
                'tunggakan_bunga'  => $this->cleanNumber($row[16] ?? 0),    // Kolom Q
                'hari_bunga'       => is_numeric($row[17] ?? null) ? (int)$row[17] : 0, // Kolom R
                'denda'            => $this->cleanNumber($row[18] ?? 0),    // Kolom S
                'bakidebet'        => $this->cleanNumber($row[19] ?? 0),    // Kolom T (Total Tunggakan)
                
                'kol'              => $this->currentKol,
                'bulan'            => $this->labelBulan,
            ]
        );
    }

    private function cleanNumber($value) {
        if (empty($value)) return 0;
        if (is_numeric($value)) return (float) $value;
        // Hapus karakter non-angka kecuali titik desimal dan minus
        $clean = str_replace(['.', ','], ['', ''], $value);
        return (float) preg_replace('/[^0-9.-]/', '', $clean);
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