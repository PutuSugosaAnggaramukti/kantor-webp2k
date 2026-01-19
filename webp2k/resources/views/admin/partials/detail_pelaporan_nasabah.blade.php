<div class="page-title" style="margin-bottom: 25px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #000; margin-bottom: 5px;">Data Pelaporan Nasabah</h2>
    <p style="font-size: 14px; font-weight: 600;">
        Dashboard <span style="margin: 0 5px;">></span> Pelaporan <span style="margin: 0 5px;">></span> <span style="color: #007bff;">Wahyu</span>
    </p>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <button onclick="openModalExport('modalExportPelaporan')" style="background-color: #4CAF50; color: white; border: none; padding: 8px 20px; border-radius: 20px; font-weight: 700; display: flex; align-items: center; cursor: pointer;">
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
                <th style="padding: 15px; border-right: 2px solid #000; width: 150px;">Tanggal</th>
                <th style="padding: 15px; border-right: 2px solid #000; width: 150px;">No.Ang</th>
                <th style="padding: 15px;">Nama</th>
            </tr>
        </thead>
        <tbody style="font-weight: 700; font-size: 14px;">
            @php
                // Data dummy sesuai gambar referensi
                $detailNasabah = [
                    ['tgl' => '2025-12-01', 'no_ang' => '20002347', 'nama' => 'HENI SUSILONINGSIH DRA'],
                    ['tgl' => '2025-12-02', 'no_ang' => '20000228', 'nama' => 'EKO SUTRISNO AJI'],
                    ['tgl' => '2025-12-03', 'no_ang' => '20002225', 'nama' => 'INGRAM SUHARTO'],
                    ['tgl' => '2025-12-03', 'no_ang' => '21002253', 'nama' => 'SUPARDI'],
                    ['tgl' => '2025-12-04', 'no_ang' => '22002666', 'nama' => 'MUJINAH'],
                    ['tgl' => '2025-12-05', 'no_ang' => '19000718', 'nama' => 'FELIX DODY YULIANTO'],
                ];
            @endphp

            @foreach($detailNasabah as $index => $item)
            <tr style="border-bottom: 2px solid #000; text-align: center;">
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $index + 1 }}</td>
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $item['tgl'] }}</td>
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $item['no_ang'] }}</td>
                <td style="padding: 15px; text-align: left; padding-left: 20px; text-transform: uppercase;">
                    {{ $item['nama'] }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>