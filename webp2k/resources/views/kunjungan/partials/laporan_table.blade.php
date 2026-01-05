<div class="page-title">
    <h2>Laporan Kunjungan</h2>
    <div class="breadcrumb">
       <a href="/dashboard">Dashboard > </a> <span style="color: #3b82f6;">Laporan Kunjungan</span>
    </div>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <button class="btn-excel">
        <i class="fa-solid fa-file-excel"></i> Excel
    </button>
    <div style="position: relative;">
        <input type="text" placeholder="Pencarian.." style="padding: 8px 30px 8px 15px; border-radius: 20px; border: 1px solid #ddd; outline: none;">
        <i class="fa-solid fa-magnifying-glass" style="position: absolute; right: 10px; top: 10px; color: #ccc;"></i>
    </div>
</div>

<div class="table-responsive" style="width: 100%; overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; background: white; border: 1px solid #333;">
        <thead>
            <tr style="background-color: #f5f5f5; text-align: center;">
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 60px;">No</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 120px;">Kode</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700;">Nasabah</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 100px;">KOL</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 150px;">Bulan</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 150px;">Kunjungan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $laporanDummy = [
                    ['no' => 1, 'kode' => 'PG.803', 'nama' => 'SUPARDI', 'kol' => 'LANCAR', 'status' => 'done'],
                    ['no' => 2, 'kode' => 'PG.803', 'nama' => 'MUJINAH', 'kol' => 'LANCAR', 'status' => 'done'],
                    ['no' => 3, 'kode' => 'PG.803', 'nama' => 'MUJIYATNO', 'kol' => 'LANCAR', 'status' => 'done'],
                    ['no' => 4, 'kode' => 'PG.803', 'nama' => 'SUNARDI', 'kol' => 'DPK', 'status' => 'done'],
                    ['no' => 5, 'kode' => 'PG.803', 'nama' => 'HARTO', 'kol' => 'DPK', 'status' => 'pending'],
                    ['no' => 6, 'kode' => 'PG.803', 'nama' => 'PARJILAN', 'kol' => 'DPK', 'status' => 'pending'],
                ];
            @endphp

            @foreach($laporanDummy as $item)
            <tr style="text-align: center;">
                <td style="border: 1px solid #333; padding: 15px; font-weight: 700;">{{ $item['no'] }}</td>
                <td style="border: 1px solid #333; padding: 15px; font-weight: 700;">{{ $item['kode'] }}</td>
                <td style="border: 1px solid #333; padding: 15px; font-weight: 700; text-align: left;">{{ $item['nama'] }}</td>
                <td style="border: 1px solid #333; padding: 15px; font-weight: 700;">{{ $item['kol'] }}</td>
                <td style="border: 1px solid #333; padding: 15px; font-weight: 700;">NOV 2025</td>
                <td style="border: 1px solid #333; padding: 10px;">
                    <div style="display: flex; justify-content: center;">
                        @if($item['status'] == 'done')
                            <button onclick="loadContent('{{ route('kunjungan.bukti', $item['no']) }}')" 
                                class="status-circle bg-success" 
                                style="border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i class="fa-solid fa-check"></i>
                        </button>
                        @else
                            <div class="status-circle bg-pending">
                                <i class="fa-solid fa-minus"></i>
                            </div>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>