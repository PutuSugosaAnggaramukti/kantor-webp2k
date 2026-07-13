<style>
    /* Paksa menu Data Kunjungan aktif */
    #menu-data {
        background-color: white !important;
        color: #4e4bc1 !important;
        font-weight: bold !important;
        border-radius: 10px 0 0 10px !important;
    }
    #menu-data i {
        color: #4e4bc1 !important;
    }
    
    /* Matikan paksa menu Laporan jika sedang di halaman ini */
    #menu-laporan {
        background-color: transparent !important;
        color: white !important;
    }

    /* Style untuk baris yang sudah dikunjungi */
    .row-completed {
        background-color: #f0fdf4 !important; /* Hijau sangat muda */
    }
    
    .badge-status {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: bold;
        margin-left: 10px;
    }

    .row-completed {
        background-color: #f0fdf4 !important;
    }
    
    /* Warna untuk baris yang BELUM terisi (Merah) */
    .row-pending {
        background-color: #fff1f2 !important; /* Merah muda sangat halus */
    }

    .badge-status {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: bold;
        margin-left: 10px;
    }
</style>

<div class="page-title">
    <h2>Data Kunjungan</h2>
    <div class="breadcrumb">
       <a href="/user/dashboard">Dashboard > </a> <span style="color: #3b82f6;">Data Kunjungan</span>
    </div>
</div>

<div class="header-actions">
    
    <button onclick="openModalAO()" class="btn-action-custom" style="background-color: #4e4bc1; color: white;">
        <i class="fa-solid fa-plus-circle"></i> 
        <span>Tambah Jadwal Kunjungan</span>
    </button>

    <button onclick="openModalUbahJadwal()" class="btn-action-custom" style="background-color: #ffc107; color: #212529;">
        <i class="fas fa-calendar-alt"></i>
        <span>Ubah Jadwal Kunjungan</span>
    </button>

    <div class="search-wrapper">
        <input type="text" 
               id="searchNasabah"
               onkeyup="..."
               placeholder="Cari nasabah.." 
               style="width: 100%; padding: 8px 35px 8px 15px; border-radius: 20px; border: 1px solid #ddd; outline: none; background-color: #f9f9f9; height: 40px;">
        <i class="fa-solid fa-magnifying-glass" style="position: absolute; right: 12px; top: 12px; color: #ccc;"></i>
    </div>

</div>

@if(session('success'))
    <div style="padding: 15px; background-color: #d1edda; color: #155724; border-radius: 10px; margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="padding: 15px; background-color: #f8d7da; color: #721c24; border-radius: 10px; margin-bottom: 20px;">
        {{ session('error') }}
    </div>
@endif

<div class="table-responsive" style="width: 100%; overflow-x: auto;">
    <table id="tableKunjungan" style="width: 100%; border-collapse: collapse; background: white; border: 1px solid #333;">
        <thead>
            <tr style="background-color: #f5f5f5; text-align: center;">
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 60px;">No</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 120px;">No Angsuran</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700;">Nasabah</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 180px;">Tgl Kunjungan</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 150px;">Bulan</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 200px;">Option</th> {{-- Lebar ditambah untuk 3 tombol --}}
            </tr>
        </thead>
      <tbody style="font-weight: 800; font-size: 16px; color: #000;">
            @forelse($data as $index => $item)
            @php
                $isFilled = $item->is_filled ?? false;
            @endphp
                {{-- Baris akan berwarna hijau (#f0fff4) hanya jika No Angsuran & Tanggal cocok di tabel laporan --}}
                <tr class="{{ $isFilled ? 'row-completed' : 'row-pending' }}" 
                    style="border-bottom: 2px solid #000; text-align: center; {{ $isFilled ? 'background-color: #f0fff4;' : '' }}">
                    
                    <td style="padding: 15px; border-right: 2px solid #000;">
                        {{ ($data->currentPage() - 1) * $data->perPage() + $index + 1 }}
                    </td>
                    
                    <td style="padding: 15px; border-right: 2px solid #000;">{{ $item->no_angsuran ?? '-'}}</td>
                    
                    <td style="padding: 15px; border-right: 2px solid #000; text-align: left; padding-left: 20px;">
                        {{ $item->nama_nasabah }}
                        @if($isFilled)
                            <span class="badge-status" style="background-color: #dcfce7; color: #166534; border: 1px solid #166534; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-left: 10px;">
                                <i class="fa-solid fa-circle-check"></i> Terisi
                            </span>
                        @else
                            <span class="badge-status" style="background-color: #fee2e2; color: #991b1b; border: 1px solid #991b1b; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-left: 10px;">
                                <i class="fa-solid fa-triangle-exclamation"></i> Belum Diisi
                            </span>
                        @endif
                    </td>

                    <td style="padding: 15px; border-right: 2px solid #000; color: #007bff;">
                        @if(!empty($item->tanggal) && $item->tanggal != '0000-00-00')
                            {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}
                        @else
                            -
                        @endif
                    </td>
                    
                    {{-- Bagian kolom Bulan dan tombol opsi tetap sama seperti sebelumnya --}}
                    <td style="padding: 15px; border-right: 2px solid #000;">
                        @php
                            $valBulan = $item->bulan ?? '-';
                            $bulanTampil = $valBulan; 
                            if (!empty($valBulan) && $valBulan !== '-' && preg_match('/^[0-9]{4}-[0-9]{2}/', $valBulan)) {
                                try {
                                    $bulanTampil = \Carbon\Carbon::parse($valBulan)->translatedFormat('M Y');
                                } catch (\Exception $e) { $bulanTampil = $valBulan; }
                            }
                        @endphp
                        {{ $bulanTampil }}
                    </td>

                    <td style="border: 1px solid #333; padding: 15px;">
                        <div style="display: flex; justify-content: center; gap: 8px; align-items: center;">
                            @if($isFilled)
                                <div style="background-color: #28a745; color: white; width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 1px solid #000;">
                                    <i class="fa-solid fa-check" style="font-size: 18px;"></i>
                                </div>
                            @else
                                <button onclick="openModal('{{ $item->id }}', '{{ $item->nama_nasabah }}', '{{ $item->kode_ao }}', '{{ $item->no_angsuran }}')"
                                        style="background-color: #A3A8AC; color: #333; border: 2px solid #000; width: 35px; height: 35px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa-solid fa-plus" style="font-size: 18px;"></i>
                                </button>
                            @endif

                            <button onclick="confirmDeleteJadwal('{{ $item->id }}', '{{ $item->nama_nasabah }}')" 
                                    style="background-color: #dc3545; color: white; border: 2px solid #000; width: 35px; height: 35px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-trash-can" style="font-size: 16px;"></i>
                            </button>
                            
                            <button onclick="openDetailModal('{{ $item->no_angsuran }}')" style="background: none; border: none; cursor: pointer;">
                                <i class="fa-solid fa-circle-info" style="font-size: 32px; color: #3A3A4C;"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="padding: 20px;">Data tidak ditemukan</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Navigasi Pagination --}}
<div class="custom-pagination" style="margin-top: 20px; display: flex; justify-content: center;">
    {{ $data->links('pagination::bootstrap-4') }}
</div>

<div class="instruction-box" style="margin-top: 20px; background-color: #f1f1f1; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 20px;">
    <div class="icon-indicator" style="background-color: #28a745; color: white; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
        <i class="fa-solid fa-circle-check"></i>
    </div>
    <div class="text-indicator">
        <h4 style="margin: 0 0 5px 0; color: #1e293b; font-weight: 700; font-size: 16px;">Petunjuk !</h4>
        <p style="margin: 0; color: #475569; font-size: 14px; line-height: 1.5; font-weight: 600;">
            Baris berwarna <span style="color: #166534;">hijau</span> dan bertanda <i class="fa-solid fa-check"></i> menandakan bukti kunjungan nasabah tersebut sudah berhasil disimpan.
        </p>
    </div>
</div>

@include('kunjungan.partials.modals')

