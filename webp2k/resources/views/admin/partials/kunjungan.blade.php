<div class="page-title" style="margin-bottom: 25px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #000; margin-bottom: 5px;">Data Kunjungan</h2>
    <p style="font-size: 14px; font-weight: 600;">
        Dashboard <span style="margin: 0 5px;">></span> <span style="color: #007bff;">Data Kunjungan</span>
    </p>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <button style="background-color: #4CAF50; color: white; border: none; padding: 8px 20px; border-radius: 20px; font-weight: 700; display: flex; align-items: center; cursor: pointer;">
        <span style="margin-right: 8px;">X</span> Export
    </button>
    
    <div style="position: relative;">
        <input type="text" placeholder="Pencarian..." style="padding: 8px 15px; border-radius: 20px; border: 1px solid #ccc; width: 250px;">
    </div>
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
            @php
                // Data disesuaikan persis dengan gambar referensi
                $kunjunganData = [
                    ['kode' => 'PG.803', 'nama' => 'WAHYU', 'jumlah' => 16],
                    ['kode' => 'PG.803', 'nama' => 'ABU', 'jumlah' => 23],
                    ['kode' => 'PG.803', 'nama' => 'RIKA', 'jumlah' => 20],
                    ['kode' => 'PG.803', 'nama' => 'TEGAR', 'jumlah' => 15],
                    ['kode' => 'PG.803', 'nama' => 'IBNU', 'jumlah' => 14],
                    ['kode' => 'PG.803', 'nama' => 'NUGROHO', 'jumlah' => 12],
                ];
            @endphp

           @foreach($kunjunganData as $index => $item)
            <tr style="border-bottom: 2px solid #000; text-align: center;">
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $index + 1 }}</td>
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $item['kode'] }}</td>
                
                <td style="padding: 15px; border-right: 2px solid #000; text-align: left; padding-left: 20px;">
                    <a href="javascript:void(0)" 
                    onclick="loadAdminPage('detail-kunjungan')" 
                    style="text-decoration: none; color: #000; font-weight: bold;">
                    {{ $item['nama'] }}
                    </a>
                </td>
                                
                <td style="padding: 15px; text-align: center;">{{ $item['jumlah'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>