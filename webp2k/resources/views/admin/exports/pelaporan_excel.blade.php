<table>
    <thead>
        <tr>
            <th colspan="16" style="text-align: center; font-weight: bold; font-size: 14px;">REKAPITULASI KUNJUNGAN PER AO</th>
        </tr>
        <tr>
            <th colspan="16" style="text-align: center;">
                Periode: 
                {{ \Carbon\Carbon::parse($tglAwal)->locale('id')->translatedFormat('d F') }} 
                - 
                {{ \Carbon\Carbon::parse($tglAkhir)->locale('id')->translatedFormat('d F Y') }}
            </th>
        </tr>
        <tr></tr>
        <tr style="background-color: #f5f5f5; text-align: center; font-weight: bold;">
            <th style="border: 1px solid #000; width: 50px;">No</th>
            <th style="border: 1px solid #000; width: 100px;">Kode</th>
            <th style="border: 1px solid #000; width: 120px;">No. Ang</th>
            <th style="border: 1px solid #000; width: 180px;">Rekening Kredit</th>
            <th style="border: 1px solid #000; width: 250px;">Nama Nasabah</th>
            <th style="border: 1px solid #000; width: 300px;">Alamat</th>
            <th style="border: 1px solid #000; width: 50px;">Agunan</th>
            <th style="border: 1px solid #000; width: 100px;">Ikatan</th>
            <th style="border: 1px solid #000; width: 150px;">Sisa Pokok</th>
            <th style="border: 1px solid #000; width: 120px;">Pokok/bln</th>
            <th style="border: 1px solid #000; width: 120px;">Bunga/bln</th>
            <th style="border: 1px solid #000; width: 120px;">Kode AO</th>
            <th style="border: 1px solid #000; width: 180px;">Nama AO</th>
            <th style="border: 1px solid #000; width: 100px;">Tanggal</th>
            <th style="border: 1px solid #000; width: 300px;">Catatan Lapangan</th>
            <th style="border: 1px solid #000; width: 130px;">Tgl Janji Bayar</th>
        </tr>
    </thead>
    <tbody>
        @php $globalNo = 1; @endphp
        @foreach($data_ao as $kunj)
            <tr>
                <td style="border: 1px solid #000; text-align: center;">{{ $globalNo++ }}</td>

                <td style="border: 1px solid #000; text-align: center;">
                    {{ $kunj->kode ?? '-' }}
                </td>

                <td style="border: 1px solid #000; text-align: center;">
                    {{ $kunj->no_angsuran ?? '-' }}
                </td>

                <td style="border: 1px solid #000; text-align: center;">
                    {{ $kunj->rekening_kredit ?? '-' }}
                </td>

                <td style="border: 1px solid #000; text-transform: uppercase;">
                    {{ $kunj->nama_nasabah }}
                </td>

                <td style="border: 1px solid #000;">
                    {{ $kunj->alamat_nasabah ?? '-' }}
                </td>

                <td style="border: 1px solid #000; text-align: center;">
                    {{ $kunj->kode_agunan ?? '-' }}
                </td>

                <td style="border: 1px solid #000; text-align: center;">
                    {{ $kunj->ikatan ?? '-' }}
                </td>

                <td style="border: 1px solid #000; text-align: right;">
                    {{ $kunj->sisa_pokok_nasabah ?? 0 }}
                </td>

                <td style="border: 1px solid #000; text-align: right;">
                    {{ $kunj->pokok_per_bulan_nasabah ?? 0 }}
                </td>

                <td style="border: 1px solid #000; text-align: right;">
                    {{ $kunj->bunga_per_bulan_nasabah ?? 0 }}
                </td>

                <td style="border: 1px solid #000; text-align: center;">
                    {{ $kunj->kode_ao ?? '-' }}
                </td>

                <td style="border: 1px solid #000;">
                    {{ $kunj->nama_karyawan ?? 'N/A' }}
                </td>

                <td style="border: 1px solid #000; text-align: center;">
                    {{ \Carbon\Carbon::parse($kunj->tanggal_jadwal)->format('d-m-Y') }}
                </td>

                <td style="border: 1px solid #000; vertical-align: top;">
                    {{ $kunj->catatan ?? '-' }}
                </td>

                <td style="border: 1px solid #000; text-align: center;">
                    {{ $kunj->tgl_janji_bayar ? \Carbon\Carbon::parse($kunj->tgl_janji_bayar)->format('d-m-Y') : '-' }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>