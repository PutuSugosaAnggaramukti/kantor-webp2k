<div class="page-title" style="margin-bottom: 25px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #000; margin-bottom: 5px;">Data Karyawan</h2>
    <p style="font-size: 14px; font-weight: 600;">
        <span onclick="loadAdminPage('dashboard')" style="cursor:pointer; color:#4e4bc1;">Dashboard</span> 
        <span style="margin: 0 5px;">></span> 
        <span style="color: #007bff;">Data Karyawan</span>
    </p>
</div>

<div class="table-responsive">
    <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; background-color: #fff;">
        <thead>
            <tr style="border-bottom: 2px solid #000; text-align: center; background-color: #fcfcfc;">
                <th style="padding: 15px; border-right: 2px solid #000; width: 60px;">No</th>
                <th style="padding: 15px; border-right: 2px solid #000; width: 120px;">Kode AO</th>
                <th style="padding: 15px; border-right: 2px solid #000;">Nama</th>
                <th style="padding: 15px; width: 180px;">Jumlah Kunjungan</th>
            </tr>
        </thead>
        <tbody style="font-weight: 700; font-size: 14px;">
            @foreach($karyawan as $index => $item)
            <tr style="border-bottom: 2px solid #000; text-align: center;">
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $index + 1 }}</td>
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $item->kode_ao }}</td> <td style="padding: 15px; border-right: 2px solid #000; text-align: left; padding-left: 20px;">
                    <a href="javascript:void(0)" onclick="loadAdminPage('detail-karyawan')" style="text-decoration: none; color: #000;">
                        {{ $item->nama }}
                    </a>
                </td>
                <td style="padding: 15px; text-align: center;">{{ $item->jumlah_kunjungan ?? 0 }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>