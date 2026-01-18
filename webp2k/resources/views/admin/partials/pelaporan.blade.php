<div class="page-title" style="margin-bottom: 25px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #000; margin-bottom: 5px;">Pelaporan</h2>
    <p style="font-size: 14px; font-weight: 600;">
        Dashboard <span style="margin: 0 5px;">></span> <span style="color: #007bff;">Pelaporan</span>
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
                <th style="padding: 15px; border-right: 2px solid #000; width: 150px;">Kode AO</th>
                <th style="padding: 15px;">Nama</th>
            </tr>
        </thead>
        <tbody style="font-weight: 700; font-size: 14px;">
            @php
                $pelaporanData = [
                    ['tgl' => '2025-12-01', 'kode' => 'PG.803', 'nama' => 'WAHYU'],
                    ['tgl' => '2025-12-02', 'kode' => 'PG.804', 'nama' => 'ABU'],
                    ['tgl' => '2025-12-03', 'kode' => 'PG.805', 'nama' => 'TEGAR'],
                    ['tgl' => '2025-12-03', 'kode' => 'PG.806', 'nama' => 'NUGROHO'],
                    ['tgl' => '2025-12-04', 'kode' => 'PG.807', 'nama' => 'FAJAR'],
                    ['tgl' => '2025-12-05', 'kode' => 'PG.808', 'nama' => 'IBNU'],
                ];
            @endphp

            @foreach($pelaporanData as $index => $item)
            <tr style="border-bottom: 2px solid #000; text-align: center; background-color: #fff;">
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $index + 1 }}</td>
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $item['tgl'] }}</td>
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $item['kode'] }}</td>
                <td style="padding: 15px; text-align: left; padding-left: 20px; text-transform: uppercase;">
                    {{ $item['nama'] }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>