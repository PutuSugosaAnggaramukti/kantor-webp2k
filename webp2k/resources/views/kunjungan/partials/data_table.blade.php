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
</style>


<div class="page-title">
    <h2>Data Kunjungan</h2>
    <div class="breadcrumb">
       <a href="/user/dashboard">Dashboard > </a> <span style="color: #3b82f6;">Data Kunjungan</span>
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
        <tbody style="font-weight: 800; font-size: 16px; color: #000;">
            @forelse($data as $index => $item)
            <tr style="border-bottom: 2px solid #000; text-align: center;">
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $index + 1 }}</td>
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $item->kode_ao }}</td>
                <td style="padding: 15px; border-right: 2px solid #000; text-align: left; padding-left: 20px;">
                    {{ $item->nama_nasabah }}
                </td>
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $item->kol }}</td>
                <td style="padding: 15px; border-right: 2px solid #000;">
                    {{ \Carbon\Carbon::parse($item->bulan)->translatedFormat('M Y') }}
                </td>
                <td style="border: 1px solid #333; padding: 15px;">
                    <div style="display: flex; justify-content: center; gap: 15px; align-items: center;">
                        
                        <button onclick="openModal('{{ $item->nama_nasabah }}', '{{ $item->kode_ao }}')" 
                                style="background-color: #A3A8AC; color: #333; border: none; width: 35px; height: 35px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-plus" style="font-size: 18px;"></i>
                        </button>

                        <button onclick="openDetailModal('{{ $item->kode_ao }}', '-', '{{ $item->nama_nasabah }}', '-', '0', '0', '{{ $item->kol }}', '-', '-')" 
                                style="background: none; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-circle-info" style="font-size: 32px; color: #3A3A4C;"></i>
                        </button>
                        
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding: 20px; text-align: center;">Belum ada jadwal kunjungan untuk Anda.</td>
            </tr>
            @endforelse
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