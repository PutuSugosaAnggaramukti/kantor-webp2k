<div class="page-title">
    <h2>Data Kunjungan</h2>
    <div class="breadcrumb">
        <a href="/user/dashboard">Dashboard > </a>
        <span style="color: #3b82f6;">Data Kunjungan</span>
    </div>
</div>

<div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
    <div style="position: relative; width: 300px;">
        <input type="text" placeholder="Pencarian.." style="width: 100%; padding: 8px 35px 8px 15px; border-radius: 20px; border: 1px solid #ddd; outline: none; background-color: #f9f9f9;">
        <i class="fa-solid fa-xmark" style="position: absolute; right: 12px; top: 10px; color: #ccc; cursor: pointer;"></i>
    </div>
</div>

@if(session('success'))
    <div style="padding: 15px; background-color: #d1edda; color: #155724; border-radius: 10px; margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

<div class="table-responsive" style="width: 100%; overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; background: white; border: 1px solid #333;">
        <thead>
            <tr style="background-color: #f5f5f5; text-align: center;">
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 60px;">No</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 120px;">Kode</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700;">Nasabah</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 100px;">KOL</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 150px;">Bulan</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 150px;">Option</th>
            </tr>
        </thead>
        <tbody>
            @php
                $dummies = [
                    ['no' => 1, 'kode' => 'PG.803', 'nama' => 'SUPARDI', 'kol' => 'LANCAR'],
                    ['no' => 2, 'kode' => 'PG.803', 'nama' => 'MUJINAH', 'kol' => 'LANCAR'],
                    ['no' => 3, 'kode' => 'PG.803', 'nama' => 'MUJIYATNO', 'kol' => 'LANCAR'],
                    ['no' => 4, 'kode' => 'PG.803', 'nama' => 'SUNARDI', 'kol' => 'DPK'],
                    ['no' => 5, 'kode' => 'PG.803', 'nama' => 'HARTO', 'kol' => 'DPK'],
                    ['no' => 6, 'kode' => 'PG.803', 'nama' => 'PARJILAN', 'kol' => 'DPK'],
                ];
            @endphp

            {{-- 1. LOOP DATA DUMMY --}}
            @foreach($dummies as $d)
            <tr style="text-align: center;">
                <td style="border: 1px solid #333; padding: 15px; font-weight: 700;">{{ $d['no'] }}</td>
                <td style="border: 1px solid #333; padding: 15px; font-weight: 700;">{{ $d['kode'] }}</td>
                <td style="border: 1px solid #333; padding: 15px; font-weight: 700; text-align: left;">{{ $d['nama'] }}</td>
                <td style="border: 1px solid #333; padding: 15px; font-weight: 700;">{{ $d['kol'] }}</td>
                <td style="border: 1px solid #333; padding: 15px; font-weight: 700;">NOV 2025</td>
                <td style="border: 1px solid #333; padding: 15px;">
                    <div style="display: flex; justify-content: center; gap: 15px; align-items: center;">
                        <button class="btn-action-add" onclick="openModal('{{ $d['nama'] }}', '{{ $d['kode'] }}')">
                            <i class="fa-solid fa-plus"></i>
                        </button>

                        <button class="btn-action-info" onclick="openDetailModal('{{ $d['kode'] }}', '-', '{{ $d['nama'] }}', '-', '0', '0', '{{ $d['kol'] }}', '-', '-')">
                            i
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach

            {{-- 2. LOOP DATA DARI DATABASE DENGAN PENGAMAN --}}
            @if(isset($data) && count($data) > 0)
                @foreach($data as $key => $item)
                <tr style="text-align: center;">
                    <td style="border: 1px solid #333; padding: 15px; font-weight: 700;">{{ $key + 7 }}</td>
                    <td style="border: 1px solid #333; padding: 15px; font-weight: 700;">{{ $item->kode }}</td>
                    <td style="border: 1px solid #333; padding: 15px; font-weight: 700; text-align: left;">{{ strtoupper($item->nasabah) }}</td>
                    <td style="border: 1px solid #333; padding: 15px; font-weight: 700;">{{ $item->kol }}</td>
                    <td style="border: 1px solid #333; padding: 15px; font-weight: 700;">{{ $item->bulan }}</td>
                    <td style="border: 1px solid #333; padding: 15px;">
                        <div style="display: flex; justify-content: center; gap: 15px; align-items: center;">
                            <button class="btn-plus" onclick="openModal('{{ $item->nasabah }}', '{{ $item->kode }}')" style="background-color: #8e94a9; color: white; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer;">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                            <button class="btn-info" 
                                onclick="openDetailModal(
                                    '{{ $item->kode }}', 
                                    '{{ $item->no_angsuran }}', 
                                    '{{ $item->nasabah }}', 
                                    '{{ $item->alamat }}', 
                                    'Rp {{ number_format($item->nominal ?? 0, 0, ',', '.') }}', 
                                    'Rp {{ number_format($item->sisa_pokok ?? 0, 0, ',', '.') }}', 
                                    '{{ $item->kol }}', 
                                    '{{ $item->kode_ao }}', 
                                    '{{ $item->nama_ao }}'
                                )"
                                style="background: none; border: 2px solid #333; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-weight: 900; cursor: pointer;">
                                i
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>

<div class="instruction-box" style="margin-top: 20px; background-color: #f1f1f1; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 20px;">
    <div class="icon-indicator" style="background-color: #8e94a9; color: white; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
        <i class="fa-solid fa-plus"></i>
    </div>
    <div class="text-indicator">
        <h4 style="margin: 0 0 5px 0; color: #1e293b; font-weight: 700; font-size: 16px;">Petunjuk !</h4>
        <p style="margin: 0; color: #475569; font-size: 14px; line-height: 1.5; font-weight: 600;">
            Silahkan klik tombol tersebut pada tabel untuk mengisi form kunjungan sebagai bukti telah menyelesaikan kunjungan
        </p>
    </div>
</div>