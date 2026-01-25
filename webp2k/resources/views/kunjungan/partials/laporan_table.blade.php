<div class="page-title">
    <h2>Laporan Kunjungan</h2>
    <div class="breadcrumb">
        <a href="javascript:void(0)" onclick="loadPage('dashboard')">Dashboard > </a> 
        <span style="color: #3b82f6;">Laporan Kunjungan</span>
    </div>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <button class="btn-excel" style="background-color: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold;">
        <i class="fa-solid fa-file-excel"></i> Excel
    </button>
    <div style="position: relative;">
        <input type="text" placeholder="Pencarian.." style="padding: 8px 30px 8px 15px; border-radius: 20px; border: 1px solid #ddd; outline: none; width: 250px;">
        <i class="fa-solid fa-magnifying-glass" style="position: absolute; right: 10px; top: 10px; color: #ccc;"></i>
    </div>
</div>

<div class="table-responsive" style="width: 100%; overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; background: white; border: 2px solid #333;">
        <thead>
            <tr style="background-color: #f5f5f5; text-align: center; border-bottom: 2px solid #333;">
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 60px;">No</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 120px;">Kode</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700;">Nasabah</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 100px;">KOL</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 150px;">Bulan</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 150px;">Kunjungan</th>
            </tr>
        </thead>
        <tbody>
            {{-- Sekarang menggunakan data asli dari Controller --}}
            @forelse($laporan as $index => $item)
            <tr style="text-align: center; border-bottom: 1px solid #333;">
                <td style="border: 1px solid #333; padding: 15px; font-weight: 700;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #333; padding: 15px; font-weight: 700;">{{ $item->karyawan->kode_ao ?? '-' }}</td>
                <td style="border: 1px solid #333; padding: 15px; font-weight: 700; text-align: left; padding-left: 20px;">
                    {{ strtoupper($item->nama_nasabah) }}
                </td>
                <td style="border: 1px solid #333; padding: 15px; font-weight: 700;">{{ $item->kol }}</td>
                <td style="border: 1px solid #333; padding: 15px; font-weight: 700;">{{ $item->bulan }}</td>
                <td style="border: 1px solid #333; padding: 10px;">
                    <div style="display: flex; justify-content: center; align-items: center;">
                        @if($item->hasilKunjungan)
                            {{-- Tombol AKTIF (Sudah Lapor) --}}
                            <button onclick="loadPage('detail-pelaporan?id={{ $item->hasilKunjungan->id }}')" 
                                    style="border: none; background: #28a745; color: white; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.3s;"
                                    title="Lihat Detail Bukti Kunjungan">
                                <i class="fa-solid fa-check"></i>
                            </button>
                        @else
                            {{-- Icon PASIF (Belum Lapor) --}}
                            <div style="background-color: #007bff; color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center;"
                                 title="Belum Ada Laporan">
                                <i class="fa-solid fa-minus"></i>
                            </div>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding: 20px; text-align: center; font-weight: bold; color: #999;">Belum ada data rencana kunjungan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>