<div class="page-title" style="margin-bottom: 25px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #000; margin-bottom: 5px;">Data Pelaporan Nasabah</h2>
    <p style="font-size: 14px; font-weight: 600;">
        <span onclick="loadAdminPage('dashboard')" style="cursor: pointer;" onmouseover="this.style.color='#007bff'" onmouseout="this.style.color='#000'">Dashboard</span> 
        <span style="margin: 0 5px;">></span> 
        <span onclick="loadAdminPage('pelaporan')" style="cursor: pointer;" onmouseover="this.style.color='#007bff'" onmouseout="this.style.color='#000'">Pelaporan</span> 
        <span style="margin: 0 5px;">></span> 
        <span style="color: #007bff;">{{ $ao->nama ?? 'AO Detail' }}</span>
    </p>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <button style="background-color: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: bold; display: flex; align-items: center; gap: 8px; cursor: pointer;">
        <i class="fa-solid fa-file-excel"></i> Export
    </button>
    <input type="text" placeholder="Pencarian.." class="search-input" style="padding: 10px 15px; border-radius: 20px; border: 1px solid #ddd; width: 250px;">
</div>

<div class="table-responsive">
    <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; background-color: #fff;">
        <thead>
            <tr style="border-bottom: 2px solid #000; text-align: center; background-color: #fcfcfc;">
                <th style="padding: 15px; border-right: 2px solid #000; width: 60px;">No</th>
                <th style="padding: 15px; border-right: 2px solid #000; width: 150px;">Tanggal</th>
                <th style="padding: 15px; border-right: 2px solid #000; width: 180px;">No.Ang</th>
                <th style="padding: 15px;">Nama Nasabah</th>
            </tr>
        </thead>
        <tbody style="font-weight: 700; font-size: 14px; text-align: center;">
            @forelse($histori_ao as $index => $item)
            <tr style="border-bottom: 2px solid #000;">
                <td style="padding: 12px; border-right: 2px solid #000;">{{ $index + 1 }}</td>
                <td style="padding: 12px; border-right: 2px solid #000;">
                    {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') : '-' }}
                </td>
                <td style="padding: 12px; border-right: 2px solid #000;">
                    {{ $item->no_angsuran }}
                </td>
                <td style="padding: 12px; text-align: left; padding-left: 20px;">
                    {{ $item->nama_nasabah }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="padding: 20px;">AO ini belum melakukan kunjungan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>