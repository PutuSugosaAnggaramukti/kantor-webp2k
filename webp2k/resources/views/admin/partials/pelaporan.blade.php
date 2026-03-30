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

{{-- TABEL 1: DAFTAR AO --}}
<div style="margin-bottom: 15px;">
    <h3 style="font-size: 18px; font-weight: 700; color: #333;">Daftar AO Aktif Berkunjung</h3>
</div>
<div class="table-responsive" style="margin-bottom: 40px;">
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
                    {{ $item->kunjungan_terbaru ? \Carbon\Carbon::parse($item->kunjungan_terbaru->tanggal)->format('d-m-Y') : '-' }}
                </td>
                <td style="padding: 12px; border-right: 2px solid #000;">
                    {{ $item->kode_ao }}
                </td>
                <td style="padding: 12px; text-align: left; padding-left: 20px;">
                    <span 
                        onclick="loadAdminPage('pelaporan-detail/{{ $item->id }}')" 
                        style="cursor: pointer; color: #000; text-decoration: underline;"
                        onmouseover="this.style.color='#007bff'" 
                        onmouseout="this.style.color='#000'">
                        {{ $item->nama ?? 'Nama Tidak Ada' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="padding: 30px; text-align: center; color: #888;">
                    Tidak ada data kunjungan yang tersedia.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<hr style="border: 1px solid #ccc; margin-bottom: 30px;">

{{-- TABEL 2: REKAP NASABAH TERKUNJUNGI --}}
<div style="margin-bottom: 15px;">
    <h3 style="font-size: 18px; font-weight: 700; color: #333;">Daftar Nasabah Sudah Dikunjungi</h3>
</div>
<div class="table-responsive">
    <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; background-color: #fff;">
        <thead>
            <tr style="border-bottom: 2px solid #000; text-align: center; background-color: #fcfcfc;">
                <th style="padding: 15px; border-right: 2px solid #000; width: 60px;">No</th>
                <th style="padding: 15px; border-right: 2px solid #000; width: 150px;">No. Angsuran</th>
                <th style="padding: 15px; border-right: 2px solid #000;">Nama Nasabah</th>
                <th style="padding: 15px; border-right: 2px solid #000;">Dikunjungi Oleh (AO)</th>
                <th style="padding: 15px;">Tgl Terakhir Kunjung</th>
            </tr>
        </thead>
        <tbody style="font-weight: 700; font-size: 14px; text-align: center;">
            @forelse($nasabah_terkunjungi as $index => $nasabah)
            <tr style="border-bottom: 2px solid #000;">
                <td style="padding: 12px; border-right: 2px solid #000;">{{ $index + 1 }}</td>
                <td style="padding: 12px; border-right: 2px solid #000;">{{ $nasabah->no_angsuran }}</td>
                <td style="padding: 12px; border-right: 2px solid #000; text-align: left; padding-left: 20px; text-transform: uppercase;">
                    {{ $nasabah->nasabah }}
                </td>
                <td style="padding: 12px; border-right: 2px solid #000;">
                    @if($nasabah->kunjungan->isNotEmpty())
                        {{ $nasabah->kunjungan->first()->nama_ao }} ({{ $nasabah->kunjungan->first()->kode_ao }})
                    @else
                        -
                    @endif
                </td>
                <td style="padding: 12px;">
                    {{ $nasabah->kunjungan->isNotEmpty() ? \Carbon\Carbon::parse($nasabah->kunjungan->first()->tanggal)->format('d-m-Y') : '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding: 30px; text-align: center; color: #888;">
                    Belum ada data nasabah yang selesai dikunjungi.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>