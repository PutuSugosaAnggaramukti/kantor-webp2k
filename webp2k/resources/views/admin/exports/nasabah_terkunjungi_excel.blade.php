<table>
    <thead>
        <tr>
            <th colspan="6" style="text-align: center; font-weight: bold; font-size: 14px;">DAFTAR NASABAH SUDAH DIKUNJUNGI</th>
        </tr>
        <tr></tr>
        <tr style="background-color: #f5f5f5; text-align: center; font-weight: bold;">
            <th style="border: 1px solid #000; width: 50px;">No</th>
            <th style="border: 1px solid #000; width: 150px;">No. Angsuran</th>
            <th style="border: 1px solid #000; width: 250px;">Nama Nasabah</th>
            <th style="border: 1px solid #000; width: 200px;">Dikunjungi Oleh (AO)</th>
            <th style="border: 1px solid #000; width: 150px;">Tgl Kunjung</th>
            <th style="border: 1px solid #000; width: 300px;">Catatan</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @forelse($nasabah_terkunjungi as $nasabah)
            @if($nasabah->laporanSelesai->isNotEmpty())
                @foreach($nasabah->laporanSelesai as $kunj)
                    <tr>
                        <td style="border: 1px solid #000; text-align: center;">{{ $no++ }}</td>
                        <td style="border: 1px solid #000; text-align: center;">{{ $nasabah->no_angsuran }}</td>
                        <td style="border: 1px solid #000; text-transform: uppercase;">{{ $nasabah->nasabah }}</td>
                        <td style="border: 1px solid #000;">({{ $kunj->kode_ao }}) {{ $kunj->karyawan->nama ?? '-' }}</td>
                        <td style="border: 1px solid #000; text-align: center;">{{ \Carbon\Carbon::parse($kunj->created_at)->format('d-m-Y') }}</td>
                        <td style="border: 1px solid #000;">{{ $kunj->catatan ?? '-' }}</td>
                    </tr>
                @endforeach
            @endif
        @empty
            <tr>
                <td colspan="6" style="border: 1px solid #000; text-align: center; padding: 20px; color: #888;">Belum ada data nasabah yang dikunjungi.</td>
            </tr>
        @endforelse
    </tbody>
</table>
