<div class="page-title" style="margin-bottom: 25px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #000; margin-bottom: 5px;">Input Jadwal Kunjungan</h2>
    <p style="font-size: 14px; font-weight: 600;">
        <span onclick="loadAdminPage('dashboard')" style="cursor:pointer; color:#4e4bc1;">Dashboard</span> 
        <span style="margin: 0 5px;">></span> 
        <span style="color: #007bff;">Jadwal Kunjungan</span>
    </p>
</div>

<div style="margin-bottom: 20px;">
    <button onclick="openModalKunjungan()" style="background-color: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <i class="fa-solid fa-plus"></i> Tambah
    </button>
</div>

<div class="table-responsive">
    <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; background-color: #fff;">
        <thead>
            <tr style="border-bottom: 2px solid #000; text-align: center; background-color: #fcfcfc;">
                <th style="padding: 15px; border-right: 2px solid #000; width: 60px;">No</th>
                <th style="padding: 15px; border-right: 2px solid #000; width: 120px;">Bulan</th>
                <th style="padding: 15px; border-right: 2px solid #000;">Nama AO</th>
                <th style="padding: 15px; border-right: 2px solid #000;">Nama Nasabah</th>
                <th style="padding: 15px; border-right: 2px solid #000; width: 80px;">KOL</th>
                <th style="padding: 15px; width: 100px;">Option</th>
            </tr>
        </thead>
        <tbody style="font-weight: 800; font-size: 16px; color: #000;">
            @foreach($kunjungans as $index => $item)
            <tr style="border-bottom: 2px solid #000; text-align: center;">
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $index + 1 }}</td>
                <td style="padding: 15px; border-right: 2px solid #000;">
                    {{ \Carbon\Carbon::parse($item->bulan)->translatedFormat('F Y') }}
                </td>
                <td style="padding: 15px; border-right: 2px solid #000; text-align: left; padding-left: 20px;">
                    {{ $item->karyawan->nama ?? 'N/A' }}
                </td>
                <td style="padding: 15px; border-right: 2px solid #000; text-align: left; padding-left: 20px;">
                    {{ $item->nama_nasabah }}
                </td>
                <td style="padding: 15px; border-right: 2px solid #000;">
                    {{ $item->kol }}
                </td>
                <td style="padding: 15px; display: flex; justify-content: center; gap: 15px; border: none;">
                    <button onclick="openModalEditKunjungan('{{ $item->id }}')" style="background: none; border: none; cursor: pointer;">
                        <i class="fa-solid fa-pen-to-square" style="font-size: 20px; color: #333;"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>