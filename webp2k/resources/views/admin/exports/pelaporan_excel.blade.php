<table>
    <thead>
        <tr>
            <th colspan="5" style="text-align: center; font-weight: bold; font-size: 14px;">REKAPITULASI KUNJUNGAN PER AO</th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: center;">
                Periode: 
                {{ \Carbon\Carbon::parse($tglAwal)->locale('id')->translatedFormat('d F') }} 
                - 
                {{ \Carbon\Carbon::parse($tglAkhir)->locale('id')->translatedFormat('d F Y') }}
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach($data_ao as $ao)
            <tr><td colspan="5"></td></tr>
            <tr style="background-color: #e3f2fd;">
                <td style="font-weight: bold; border: 1px solid #000;">NAMA AO:</td>
                <td colspan="4" style="font-weight: bold; border: 1px solid #000;">{{ $ao->nama }} ({{ $ao->kode_ao }})</td>
            </tr>

            <tr style="background-color: #f5f5f5; text-align: center; font-weight: bold;">
                <th style="border: 1px solid #000;">No</th>
                <th style="border: 1px solid #000;">Tanggal</th>
                <th style="border: 1px solid #000;">No. Angsuran</th>
                <th style="border: 1px solid #000;">Nama Nasabah</th>
                <th style="border: 1px solid #000;">Catatan Lapangan</th>
            </tr>

            @foreach($ao->kunjungan as $index => $nasabah)
            <tr>
                <td style="border: 1px solid #000; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #000; text-align: center;">
                    {{ \Carbon\Carbon::parse($nasabah->tanggal)->format('d-m-Y') }}
                </td>
                <td style="border: 1px solid #000; text-align: center;">{{ $nasabah->no_angsuran }}</td>
                <td style="border: 1px solid #000;">{{ $nasabah->nama_nasabah }}</td>
              <td style="border: 1px solid #000;">
    @php
        // Kita cari catatan di tabel 'kunjungans' berdasarkan Nama Nasabah
        // agar lebih akurat dibanding nomor nasabah yang mungkin berbeda format
        $catatanLangsung = \DB::table('kunjungans')
            ->where('nama_nasabah', $nasabah->nama_nasabah)
            ->orderBy('created_at', 'desc') // Ambil yang paling baru
            ->value('catatan');
    @endphp

    {{-- Tampilkan catatan jika ditemukan, jika tidak ada baru tampilkan '-' --}}
    {{ $catatanLangsung ?? '-' }}
</td>
            </tr>
            @endforeach
            
            <tr style="font-weight: bold; background-color: #fff9c4;">
                <td colspan="4" style="border: 1px solid #000; text-align: right;">Total Kunjungan {{ $ao->nama }}:</td>
                <td style="border: 1px solid #000; text-align: center;">{{ $ao->kunjungan->count() }}</td>
            </tr>
        @endforeach
    </tbody>
</table>