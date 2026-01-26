<!DOCTYPE html>
<html>
<head>
    <title>Laporan Kunjungan Lengkap</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .section-title { background: #f2f2f2; padding: 8px; font-weight: bold; margin-bottom: 10px; border-left: 5px solid #3b82f6; }
        
        /* Gaya Tabel Laporan */
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        table, th, td { border: 1px solid #999; }
        th { background-color: #f8f9fa; padding: 10px; font-size: 12px; }
        td { padding: 10px; font-size: 12px; text-align: center; }
        .text-left { text-align: left; }

        /* Gaya Detail Bukti */
        .detail-box { margin-top: 20px; }
        .foto-kunjungan { margin-top: 15px; text-align: center; }
        .img-bukti { width: 350px; border-radius: 10px; border: 1px solid #ddd; }
        .footer { margin-top: 50px; text-align: right; font-size: 12px; }
    </style>
</head>
<body>

    <div class="header">
        <h2 style="margin:0;">LAPORAN HASIL KUNJUNGAN</h2>
        {{-- Bagian ini sekarang otomatis mengambil nama AO yang login --}}
        <p style="margin:5px 0 0 0; font-weight: bold; color: #444;">
            Bagian P2K - AO: {{ $namaAO }}
        </p>
    </div>

    <div class="section-title">I. RINGKASAN DATA NASABAH</div>
    <table>
        <thead>
            <tr>
                <th>KODE AO</th>
                <th>NAMA NASABAH</th>
                <th>KOL</th>
                <th>BULAN</th>
                <th>STATUS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $detail->dataKunjungan->kode_ao }}</td>
                <td class="text-left">{{ $detail->dataKunjungan->nama_nasabah }}</td>
                <td>{{ $detail->dataKunjungan->kol }}</td>
                <td>{{ \Carbon\Carbon::parse($detail->dataKunjungan->bulan)->translatedFormat('F Y') }}</td>
                <td style="color: green; font-weight: bold;">SELESAI</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">II. DETAIL HASIL KUNJUNGAN Lapangan</div>
    <div class="detail-box">
        <table style="border: none;">
            <tr style="border: none;">
                <td style="border: none; text-align: left; width: 30%;"><strong>Tanggal Input</strong></td>
                <td style="border: none; text-align: left;">: {{ $detail->created_at->format('d/m/Y H:i') }} WIB</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; text-align: left;"><strong>Koordinat Lokasi</strong></td>
                <td style="border: none; text-align: left;">: {{ $detail->koordinat }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; text-align: left;"><strong>Catatan AO</strong></td>
                <td style="border: none; text-align: left;">: {{ $detail->catatan }}</td>
            </tr>
        </table>

        <div class="foto-kunjungan">
            <p style="text-align: left;"><strong>Dokumentasi Foto:</strong></p>
            @if($detail->foto_kunjungan)
                {{-- Gunakan public_path agar gambar terbaca sistem PDF --}}
                <img src="{{ public_path('storage/' . $detail->foto_kunjungan) }}" class="img-bukti">
            @else
                <p style="color: #999; font-style: italic;">(Tidak ada foto terlampir)</p>
            @endif
        </div>
    </div>

    <div class="footer">
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
        <br><br><br>
        <p>(__________________________)</p>
        <p>Account Officer</p>
    </div>

</body>
</html>