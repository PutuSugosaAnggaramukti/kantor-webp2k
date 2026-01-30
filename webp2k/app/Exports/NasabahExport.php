<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings; // Tambahkan ini
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing; // Tambahkan ini
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NasabahExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithDrawings
{
    protected $tglAwal, $tglAkhir;
    private $rows; // Simpan data di sini agar sinkron antara tabel dan gambar

    public function __construct($tglAwal, $tglAkhir)
    {
        $this->tglAwal = $tglAwal;
        $this->tglAkhir = $tglAkhir;
    }

    public function collection()
    {
        // Ambil data dan simpan ke variabel $this->rows agar index-nya sama saat proses drawing
        $this->rows = DB::table('kunjungans')
            ->whereDate('created_at', '>=', $this->tglAwal)
            ->whereDate('created_at', '<=', $this->tglAkhir)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->rows;
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Kode AO', 'Nama AO', 'No Angsuran', 'Nama Nasabah', 'Alamat', 'KOL', 'Foto Kunjungan'];
    }

    public function map($row): array
    {
        static $no = 1;
        return [
            $no++,
            $row->created_at,
            $row->kode_ao,
            'WAHYU N', 
            $row->no_nasabah, 
            $row->nama_nasabah,
            $row->catatan ?? '-', 
            $row->ada_di_lokasi == 'Ada' ? '1' : '3', 
            '', // Kolom I dikosongkan untuk tempat gambar
        ];
    }

    public function drawings()
    {
        // PENTING: Foto 2MB butuh memori besar. Kita naikkan limitnya di sini.
        ini_set('memory_limit', '1024M'); 
        set_time_limit(300);

        $drawings = [];
        
        foreach ($this->rows as $index => $row) {
            // Gunakan public_path agar Excel membaca langsung dari server, bukan via URL
            $fotoPath = public_path('uploads/kunjungan/' . $row->foto_kunjungan);

            if ($row->foto_kunjungan && file_exists($fotoPath)) {
                try {
                    $drawing = new Drawing();
                    $drawing->setName('Foto');
                    $drawing->setDescription($row->nama_nasabah);
                    $drawing->setPath($fotoPath);
                    $drawing->setHeight(80); // Tinggi gambar dalam pixel
                    
                    // Koordinat I (kolom ke-9). Index data 0 berarti baris Excel 2
                    $drawing->setCoordinates('I' . ($index + 2));
                    $drawing->setOffsetX(5);
                    $drawing->setOffsetY(5);
                    $drawings[] = $drawing;
                } catch (\Exception $e) {
                    // Jika satu gambar bermasalah, jangan hentikan proses export
                    continue;
                }
            }
        }
        return $drawings;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->rows) + 1;

        // Atur lebar kolom foto
        $sheet->getColumnDimension('I')->setWidth(30);

        // Atur tinggi baris agar gambar tidak menumpuk
        if ($lastRow > 1) {
            for ($i = 2; $i <= $lastRow; $i++) {
                $sheet->getRowDimension($i)->setRowHeight(70);
            }
        }

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}