<div class="page-title" style="margin-bottom: 25px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #000; margin-bottom: 5px;">Data Kunjungan</h2>
    <p style="font-size: 14px; font-weight: 600;">
        Dashboard > Data Kunjungan > <span style="color: #007bff;">Detail Kunjungan</span>
    </p>
</div>

<div class="table-responsive">
    <table style="width: 100%; border-collapse: collapse; border: 2px solid #000;">
        <thead>
            <tr style="background: #f0f0f0; border-bottom: 2px solid #000;">
                <th style="padding: 10px; border-right: 2px solid #000;">No</th>
                <th style="padding: 10px; border-right: 2px solid #000;">Tanggal</th>
                <th style="padding: 10px; border-right: 2px solid #000;">No. Angsuran</th>
                <th style="padding: 10px; border-right: 2px solid #000;">Nama Nasabah</th>
                <th style="padding: 10px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data_detail as $item)
            <tr style="border-bottom: 2px solid #000; text-align: center;">
                <td style="padding: 10px; border-right: 2px solid #000;">{{ $loop->iteration }}</td>
                
                <td style="padding: 10px; border-right: 2px solid #000;">
                    {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') : 'Belum Input' }}
                </td>
                
                <td style="padding: 10px; border-right: 2px solid #000;">
                    {{ $item->no_angsuran ?? '-' }}
                </td>
                
                <td style="padding: 10px; border-right: 2px solid #000; text-align: left;">
                    {{ $item->nama_nasabah ?? '-' }}
                </td>
                
                <td style="padding: 10px;">
                    <button style="border-radius: 50%; width: 25px; height: 25px;">i</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding: 20px;">Data kunjungan tidak ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>