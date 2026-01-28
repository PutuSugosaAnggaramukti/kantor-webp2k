<div class="page-title" style="margin-bottom: 25px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #000; margin-bottom: 5px;">Pelaporan</h2>
    <p style="font-size: 14px; font-weight: 600;">
        <span onclick="window.location.href='/admin/dashboard'" style="cursor:pointer; color:#4e4bc1;">Dashboard</span> 
        <span style="margin: 0 5px;">></span> 
        <span style="color: #007bff;">Pelaporan</span>
    </p>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <button onclick="openModalExportPelaporan()" style="background-color: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: bold; display: flex; align-items: center; gap: 8px; cursor: pointer;">
        <i class="fa-solid fa-file-excel"></i> Export Excel
    </button>
    <input type="text" placeholder="Pencarian.." class="search-input" style="padding: 10px 15px; border-radius: 20px; border: 1px solid #ddd; width: 250px;">
</div>

<div class="table-responsive">
    <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; background-color: #fff;">
        <thead>
            <tr style="border-bottom: 2px solid #000; text-align: center; background-color: #fcfcfc;">
                <th style="padding: 15px; border-right: 2px solid #000; width: 60px;">No</th>
                <th style="padding: 15px; border-right: 2px solid #000; width: 180px;">Tanggal Kunjungan</th>
                <th style="padding: 15px; border-right: 2px solid #000; width: 150px;">Kode AO</th>
                <th style="padding: 15px;">Nama AO</th>
            </tr>
        </thead>
        <tbody style="font-weight: 700; font-size: 14px; text-align: center;">
            @forelse($pelaporan_all as $index => $item)
            <tr style="border-bottom: 2px solid #000;">
                <td style="padding: 12px; border-right: 2px solid #000;">{{ $index + 1 }}</td>
                <td style="padding: 12px; border-right: 2px solid #000;">
                    {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') : '-' }}
                </td>
                <td style="padding: 12px; border-right: 2px solid #000;">
                    {{ $item->karyawan->kode_ao ?? $item->kode_ao }}
                </td>
                <td style="padding: 12px; text-align: left; padding-left: 20px;">
                    <span 
                        onclick="loadAdminPage('pelaporan-detail/{{ $item->karyawan_id ?? $item->kode_ao }}')" 
                        style="cursor: pointer; color: #000; text-decoration: underline;"
                        onmouseover="this.style.color='#007bff'" 
                        onmouseout="this.style.color='#000'">
                        {{ $item->karyawan->nama ?? 'Nama Tidak Terdeteksi' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="padding: 30px; text-align: center; color: #888;">
                    Tidak ada data kunjungan yang tersedia untuk dilaporkan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>