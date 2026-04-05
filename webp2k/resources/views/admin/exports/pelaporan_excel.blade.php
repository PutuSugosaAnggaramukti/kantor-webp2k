<table>
    <thead>
        <tr>
            <th colspan="7" style="text-align: center; font-weight: bold; font-size: 14px;">REKAPITULASI KUNJUNGAN PER AO</th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: center;">
                Periode: 
                {{ \Carbon\Carbon::parse($tglAwal)->locale('id')->translatedFormat('d F') }} 
                - 
                {{ \Carbon\Carbon::parse($tglAkhir)->locale('id')->translatedFormat('d F Y') }}
            </th>
        </tr>
        <tr></tr> 
        <tr style="background-color: #f5f5f5; text-align: center; font-weight: bold;">
            <th style="border: 1px solid #000; width: 50px;">No</th>
            <th style="border: 1px solid #000; width: 100px;">Kode AO</th>
            <th style="border: 1px solid #000; width: 200px;">Nama AO</th>
            <th style="border: 1px solid #000; width: 100px;">Tanggal</th>
            <th style="border: 1px solid #000; width: 150px;">No. Angsuran</th>
            <th style="border: 1px solid #000; width: 250px;">Nama Nasabah</th>
            <th style="border: 1px solid #000; width: 300px;">Catatan Lapangan</th>
        </tr>
    </thead>
    <tbody>
        @php $globalNo = 1; @endphp
        @foreach($data_ao as $kunj)
            <tr>
                <td style="border: 1px solid #000; text-align: center;">{{ $globalNo++ }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ $kunj->kode_ao }}</td>
                <td style="border: 1px solid #000;">{{ $kunj->karyawan->nama ?? 'N/A' }}</td>
                <td style="border: 1px solid #000; text-align: center;">
                    {{ \Carbon\Carbon::parse($kunj->tanggal)->format('d-m-Y') }}
                </td>
                <td style="border: 1px solid #000; text-align: center;">{{ $kunj->no_angsuran }}</td>
                <td style="border: 1px solid #000; text-transform: uppercase;">{{ $kunj->nama_nasabah }}</td>
                
                {{-- --- BAGIAN PERBAIKAN CATATAN MULAI DISINI --- --}}
             <td style="border: 1px solid #000;">
                @php
                    $catatanReal = '-';
                    try {
                        // Kita cari di tabel 'kunjungans' menggunakan nama kolom yang benar:
                        // 'no_nasabah' (sebagai pengganti no_angsuran) dan 'catatan'
                        $dataKunj = \DB::table('kunjungans')
                            ->where('no_nasabah', $kunj->no_angsuran) // Data dari $kunj->no_angsuran dicocokkan ke no_nasabah
                            ->where('kode_ao', $kunj->kode_ao)
                            ->first();

                        if ($dataKunj) {
                            $catatanReal = $dataKunj->catatan; // Nama kolomnya adalah 'catatan'
                        }
                    } catch (\Exception $e) {
                        $catatanReal = '-'; 
                    }
                @endphp
                
                {{ $catatanReal ?? '-' }}
            </td>
                {{-- --- BAGIAN PERBAIKAN CATATAN SELESAI --- --}}
            </tr>
        @endforeach
    </tbody>
</table>