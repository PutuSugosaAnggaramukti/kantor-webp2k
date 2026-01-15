<div class="page-title" style="margin-bottom: 25px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #000; margin-bottom: 5px;">Data Nasabah</h2>
    <p style="font-size: 14px; font-weight: 600;">
        Dashboard <span style="margin: 0 5px;">></span> 
        Data Nasabah <span style="margin: 0 5px;">></span> 
        <span style="color: #007bff;">Pengunjung</span>
    </p>
</div>

<div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
    <input type="text" placeholder="Pencarian.." class="search-input" style="padding: 10px 15px; border-radius: 20px; border: 1px solid #ddd; width: 250px;">
</div>

<div class="table-responsive">
    <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; background-color: #fff;">
        <thead>
            <tr style="border-bottom: 2px solid #000; text-align: center; background-color: #fcfcfc;">
                <th style="padding: 15px; border-right: 2px solid #000; width: 60px;">No</th>
                <th style="padding: 15px; border-right: 2px solid #000; width: 150px;">Tanggal</th>
                <th style="padding: 15px; border-right: 2px solid #000; width: 150px;">Kode AO</th>
                <th style="padding: 15px;">Nama</th>
            </tr>
        </thead>
        <tbody style="font-weight: 700; font-size: 14px; text-align: center;">
            @php
                $detailData = [
                    ['tgl' => '2025-12-01', 'kode' => 'PG.803', 'nama' => 'WAHYU'],
                    ['tgl' => '2025-12-02', 'kode' => 'PG.804', 'nama' => 'ABU'],
                    ['tgl' => '2025-12-03', 'kode' => 'PG.805', 'nama' => 'TEGAR'],
                    ['tgl' => '2025-12-03', 'kode' => 'PG.806', 'nama' => 'NUGROHO'],
                    ['tgl' => '2025-12-04', 'kode' => 'PG.807', 'nama' => 'FAJAR'],
                    ['tgl' => '2025-12-05', 'kode' => 'PG.808', 'nama' => 'IBNU'],
                ];
            @endphp

            @foreach($detailData as $index => $item)
            <tr style="border-bottom: 2px solid #000;">
                <td style="padding: 12px; border-right: 2px solid #000;">{{ $index + 1 }}</td>
                <td style="padding: 12px; border-right: 2px solid #000;">{{ $item['tgl'] }}</td>
                <td style="padding: 12px; border-right: 2px solid #000;">{{ $item['kode'] }}</td>
                <td style="padding: 12px; text-align: left; padding-left: 20px;">{{ $item['nama'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
