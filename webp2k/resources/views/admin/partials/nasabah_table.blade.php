<div class="page-title" style="margin-bottom: 25px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #000; margin-bottom: 5px;">Data Nasabah</h2>
    <p style="font-size: 14px; font-weight: 600;">
        Dashboard <span style="margin: 0 5px;">></span> <span style="color: #007bff;">Data Nasabah</span>
    </p>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div style="display: flex; gap: 10px;">
       <button onclick="openModalExport()" class="btn-action-green" style="background-color: #4CAF50; color: white; border: none; padding: 8px 20px; border-radius: 12px; font-weight: 700; display: flex; align-items: center; cursor: pointer;">
            <span style="margin-right: 8px;">X</span> Export
        </button>
       <button onclick="openModalFilter()" class="btn-action-green" style="background-color: #4CAF50; color: white; border: none; padding: 8px 20px; border-radius: 12px; font-weight: 700; display: flex; align-items: center; cursor: pointer;">
        <i class="fa-solid fa-sliders" style="margin-right: 8px;"></i> Filter
       </button>
    </div>
    
    <input type="text" placeholder="Pencarian.." class="search-input" style="padding: 10px 15px; border-radius: 20px; border: 1px solid #ddd; width: 250px;">
</div>

<div class="table-responsive">
    <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; background-color: #fff;">
        <thead>
            <tr style="border-bottom: 2px solid #000; text-align: center; background-color: #fcfcfc;">
                <th style="padding: 15px; border-right: 2px solid #000; width: 60px;">No</th>
                <th style="padding: 15px; border-right: 2px solid #000; width: 120px;">No.Ang</th>
                <th style="padding: 15px; border-right: 2px solid #000;">Nama</th>
                <th style="padding: 15px; border-right: 2px solid #000; width: 180px;">Jml Pengunjung</th>
                <th style="padding: 15px; width: 150px;">Option</th>
            </tr>
        </thead>
        <tbody style="font-weight: 700; font-size: 14px; text-align: center;">
            @php
                $nasabahData = [
                    ['ang' => '20002347', 'nama' => 'HENI SUSILO', 'jml' => 2],
                    ['ang' => '20000228', 'nama' => 'EKO SUTRISNO', 'jml' => 2],
                    ['ang' => '20002225', 'nama' => 'INGRAM SUHARTO', 'jml' => 3],
                    ['ang' => '21002553', 'nama' => 'SUPARDI', 'jml' => 3],
                    ['ang' => '22002666', 'nama' => 'MUJINAH', 'jml' => 4],
                    ['ang' => '240001114', 'nama' => 'NAWAWI', 'jml' => 1],
                ];
            @endphp

            @foreach($nasabahData as $index => $item)
            <tr style="border-bottom: 2px solid #000;">
                <td style="padding: 12px; border-right: 2px solid #000;">{{ $index + 1 }}</td>
                <td style="padding: 12px; border-right: 2px solid #000;">{{ $item['ang'] }}</td>
                <td style="padding: 12px; border-right: 2px solid #000; text-align: left; padding-left: 20px;">{{ $item['nama'] }}</td>
                <td style="padding: 12px; border-right: 2px solid #000;">{{ $item['jml'] }}</td>
                <td style="padding: 12px; display: flex; justify-content: center;">
                   <button class="btn-save" 
                        onclick="loadAdminPage('pengunjung-nasabah')" style="padding: 6px 20px; border-radius: 20px; display: flex; align-items: center; gap: 8px; border: none; cursor: pointer; background-color: #3f36b1; color: white;">
                        <span style="color: #00ff88; font-weight: 900;">➜</span> 
                        <span style="font-size: 11px;">NEXT</span>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
