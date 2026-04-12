<table>
    <thead>
        <tr>
            {{-- Colspan naik jadi 14 karena kita tambah lagi 1 kolom (A) --}}
            <th colspan="14" style="text-align: center; font-weight: bold; font-size: 14px;">REKAPITULASI KUNJUNGAN PER AO</th>
        </tr>
        <tr>
            <th colspan="14" style="text-align: center;">
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
            <th style="border: 1px solid #000; width: 120px;">No. Angsuran</th>
            <th style="border: 1px solid #000; width: 180px;">Rekening Kredit</th>
            <th style="border: 1px solid #000; width: 100px;">Tanggal</th>
            
            {{-- HEADER BARU: KOLOM A --}}
            <th style="border: 1px solid #000; width: 50px;">A</th>

            <th style="border: 1px solid #000; width: 120px;">Kode AO</th>
            <th style="border: 1px solid #000; width: 120px;">Kode AO P2K</th>
            <th style="border: 1px solid #000; width: 180px;">Nama AO</th>
            <th style="border: 1px solid #000; width: 250px;">Nama Nasabah</th>
            
            <th style="border: 1px solid #000; width: 150px;">Sisa Pokok</th>
            <th style="border: 1px solid #000; width: 120px;">Pokok/bln</th>
            <th style="border: 1px solid #000; width: 120px;">Bunga/bln</th>
            
            <th style="border: 1px solid #000; width: 300px;">Catatan Lapangan</th>
        </tr>
    </thead>
    <tbody>
        @php $globalNo = 1; @endphp
        @foreach($data_ao as $kunj)
            @php
                $dataNasabah = \App\Models\Nasabah::where('no_angsuran', $kunj->no_angsuran)->first();
            @endphp
            <tr>
                <td style="border: 1px solid #000; text-align: center;">{{ $globalNo++ }}</td>
                
                <td style="border: 1px solid #000; text-align: center;">
                    {{ $dataNasabah->kode ?? '-' }}
                </td>

                <td style="border: 1px solid #000; text-align: center;">{{ $kunj->no_angsuran }}</td>
                
                <td style="border: 1px solid #000; text-align: center;">
                    {{ $dataNasabah->rekening_kredit ?? '-' }}
                </td>

                <td style="border: 1px solid #000; text-align: center;">
                    {{ \Carbon\Carbon::parse($kunj->tanggal)->format('d-m-Y') }}
                </td>

                {{-- ISI DATA BARU: KOLOM A (Kode Agunan) --}}
                <td style="border: 1px solid #000; text-align: center;">
                    {{ $dataNasabah->kode_agunan ?? '-' }}
                </td>

                <td style="border: 1px solid #000; text-align: center; font-weight: bold;">
                    {{ $dataNasabah->kode_ao_nasabah ?? '-' }}
                </td>

                <td style="border: 1px solid #000; text-align: center;">{{ $kunj->kode_ao }}</td>

                <td style="border: 1px solid #000;">{{ $kunj->karyawan->nama ?? 'N/A' }}</td>

                <td style="border: 1px solid #000; text-transform: uppercase;">{{ $kunj->nama_nasabah }}</td>

                <td style="border: 1px solid #000; text-align: right;">
                    {{ number_format($dataNasabah->sisa_pokok ?? 0, 0, ',', '.') }}
                </td>
                <td style="border: 1px solid #000; text-align: right;">
                    {{ number_format($dataNasabah->pokok_per_bulan ?? 0, 0, ',', '.') }}
                </td>
                <td style="border: 1px solid #000; text-align: right;">
                    {{ number_format($dataNasabah->bunga_per_bulan ?? 0, 0, ',', '.') }}
                </td>
                
                <td style="border: 1px solid #000; vertical-align: top;">
                    @php
                        $catatanReal = '-';
                        try {
                            $dataKunj = \DB::table('kunjungans')
                                ->where('no_nasabah', $kunj->no_angsuran)
                                ->where('kode_ao', $kunj->kode_ao)
                                ->first();
                            if ($dataKunj && !empty($dataKunj->catatan)) { 
                                $catatanReal = $dataKunj->catatan; 
                            }
                        } catch (\Exception $e) { 
                            $catatanReal = '-'; 
                        }
                    @endphp
                    {{ $catatanReal }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>