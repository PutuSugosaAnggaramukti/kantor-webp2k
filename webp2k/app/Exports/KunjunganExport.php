<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings; 
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing; 
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KunjunganExport implements FromCollection, WithHeadings, WithMapping, WithDrawings, WithStyles
{
    protected $kode_ao;

    // PERBAIKAN: Tambahkan '= null' agar tidak error saat dipanggil tanpa argumen
    public function __construct($kode_ao = null)
    {
        $this->kode_ao = $kode_ao;
    }

    public function collection()
    {
        // Mulai Query Dasar
        $query = DB::table('kunjungans')
            ->leftJoin('nasabahs', 'kunjungans.nama_nasabah', '=', 'nasabahs.nasabah')
            ->select('kunjungans.*', 'nasabahs.kol as kol_master')
            ->orderBy('kunjungans.created_at', 'desc');

        // PERBAIKAN: Hanya gunakan filter WHERE jika $kode_ao memiliki nilai
        if (!empty($this->kode_ao)) {
            $query->where('kunjungans.kode_ao', 'LIKE', '%' . $this->kode_ao . '%');
        }

        return $query->get();
    }

    // FUNGSI UNTUK MEMASUKKAN GAMBAR KE EXCEL
   public function drawings()
    {
        $drawings = [];
        $data = $this->collection();

        foreach ($data as $index => $row) {
            if ($row->foto_kunjungan) {
                // 1. Decode JSON karena sekarang isinya array foto
                $fotos = json_decode($row->foto_kunjungan, true);

                // 2. Cek apakah hasil decode adalah array dan tidak kosong
                if (is_array($fotos) && count($fotos) > 0) {
                    $offsetX = 0; // Untuk menggeser posisi jika ada lebih dari 1 foto

                    foreach ($fotos as $fotoNama) {
                        $path = public_path('uploads/kunjungan/' . $fotoNama);

                        if (file_exists($path)) {
                            $drawing = new Drawing();
                            $drawing->setName('Foto');
                            $drawing->setDescription('Foto Kunjungan');
                            $drawing->setPath($path);
                            $drawing->setHeight(80); 
                            
                            // Set koordinat di Kolom J
                            $drawing->setCoordinates('J' . ($index + 2)); 
                            
                            // 3. Atur Offset agar foto tidak menumpuk (opsional)
                            // Jika ingin menampilkan semua foto berjejer, gunakan setOffsetX
                            $drawing->setOffsetX($offsetX);
                            $offsetX += 100; // Geser 100 unit ke kanan untuk foto berikutnya

                            $drawings[] = $drawing;
                            
                            // CATATAN: Jika sel Excel terlalu sempit untuk banyak foto, 
                            // kamu bisa batasi hanya ambil foto pertama dengan menambahkan 'break;' di sini
                            // break; 
                        }
                    }
                } else {
                    // FALLBACK: Jika formatnya bukan JSON (data lama yang hanya 1 foto teks biasa)
                    $path = public_path('uploads/kunjungan/' . $row->foto_kunjungan);
                    if (file_exists($path)) {
                        $drawing = new Drawing();
                        $drawing->setPath($path);
                        $drawing->setHeight(80);
                        $drawing->setCoordinates('J' . ($index + 2));
                        $drawings[] = $drawing;
                    }
                }
            }
        }

        return $drawings;
    }

    // FUNGSI UNTUK MENGATUR TINGGI BARIS & STYLE
    public function styles(Worksheet $sheet)
    {
        // Mengatur tinggi baris default agar foto tidak terlihat tumpang tindih
        $sheet->getDefaultRowDimension()->setRowHeight(85);

        return [
            // Baris 1 (Heading) dibuat Bold dan Tengah
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
            ],
            // Semua sel dibuat rata tengah secara vertikal
            'A:J' => [
                'alignment' => ['vertical' => 'center']
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'No', 
            'Kode AO', 
            'Nama Nasabah', 
            'KOL', 
            'Bulan', 
            'Waktu Lapor', 
            'Catatan', 
            'Janji Pelunasan', 
            'Koordinat', 
            'Foto Kunjungan' // Kolom J
        ];
    }

    public function map($row): array
    {
        static $no = 1;

        return [
            $no++,
            $row->kode_ao,
            strtoupper($row->nama_nasabah),
            $row->kol ?: ($row->kol_master ?: '-'),
            \Carbon\Carbon::parse($row->created_at)->translatedFormat('F Y'),
            \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:i'),
            $row->catatan ?? '-',
            $row->tgl_janji_bayar ?? '-',
            $row->koordinat ?? '-',
            '' // Kosongkan kolom J karena akan diisi oleh objek Drawing (foto)
        ];
    }
}