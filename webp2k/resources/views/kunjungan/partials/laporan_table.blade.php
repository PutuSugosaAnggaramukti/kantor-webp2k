<div class="page-title">
    <h2>Laporan Kunjungan</h2>
    <div class="breadcrumb">
        <a href="javascript:void(0)" onclick="loadPage('dashboard')">Dashboard > </a> 
        <span style="color: #3b82f6;">Laporan Kunjungan</span>
    </div>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <a href="{{ route('export.excel') }}" 
    class="btn-excel" 
    style="background-color: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-flex; align-items: center;">
        <i class="fa-solid fa-file-excel" style="margin-right: 8px;"></i> Excel
    </a>
    <div style="position: relative;">
        <input type="text" id="searchInput" 
       onkeyup="let f=this.value.toUpperCase(); let rows=document.querySelectorAll('#laporanTable tbody tr'); rows.forEach(r => { let t=r.innerText.toUpperCase(); r.style.display=t.includes(f)?'':'none'; })" 
       placeholder="Cari Nama Nasabah atau Kode.." 
       style="padding: 8px 30px 8px 15px; border-radius: 20px; border: 1px solid #ddd; outline: none; width: 250px;">
        <i class="fa-solid fa-magnifying-glass" style="position: absolute; right: 10px; top: 10px; color: #ccc;"></i>
    </div>
</div>

<div class="table-responsive" style="width: 100%; overflow-x: auto;">
    <table id="laporanTable" style="width: 100%; border-collapse: collapse; background: white; border: 2px solid #333;">
        <thead>
            <tr style="background-color: #f5f5f5; text-align: center; border-bottom: 2px solid #333;">
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 60px;">No</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 120px;">Kode</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700;">Nasabah</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 100px;">KOL</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 150px;">Bulan</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 180px;">Status Pembayaran</th>
                <th style="border: 1px solid #333; padding: 15px; font-weight: 700; width: 150px;">Kunjungan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporan as $index => $item)
            <tr style="text-align: center; border-bottom: 1px solid #333;">
                <td style="border: 1px solid #333; padding: 15px; font-weight: 700;">{{ $laporan->firstItem() + $index }}</td>
                <td style="border: 1px solid #333; padding: 15px; font-weight: 700;">{{ $item->kode_ao }}</td>
                <td style="border: 1px solid #333; padding: 15px; font-weight: 700; text-align: left; padding-left: 20px;">
                    {{ strtoupper($item->nama_nasabah) }}
                </td>
                <td style="border: 1px solid #333; padding: 15px; font-weight: 700;">{{ $item->kol }}</td>
                <td style="border: 1px solid #333; padding: 15px; font-weight: 700;">{{ $item->bulan }}</td>
                
                <td style="border: 1px solid #333; padding: 15px;">
                    @php
                        $status = $item->status ?? 'Menunggu Pembayaran';
                        $color = '#856404'; // Coklat/Kuning (Default)
                        $bg = '#fff3cd';

                        if ($status == 'Sudah Bayar') {
                            $color = '#155724'; // Hijau
                            $bg = '#d4edda';
                        } elseif ($status == 'Gagal Bayar') {
                            $color = '#721c24'; // Merah
                            $bg = '#f8d7da';
                        }
                    @endphp
                    <span style="display: inline-block; padding: 5px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; color: {{ $color }}; background-color: {{ $bg }}; border: 1px solid {{ $color }};">
                        {{ $status }}
                    </span>
                </td>

                <td style="border: 1px solid #333; padding: 10px;">
                    <div style="display: flex; justify-content: center; align-items: center;">
                        @if($item->id_kunjungan)
                            <button onclick="loadPage('detail-pelaporan?id={{ $item->id_kunjungan }}')" 
                                    style="border: none; background: #28a745; color: white; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-check"></i>
                            </button>
                        @else
                            <div style="background-color: #007bff; color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-minus"></i>
                            </div>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="padding: 20px; text-align: center;">Belum ada data kunjungan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 20px; display: flex; justify-content: center;">
    {{ $laporan->links('pagination::bootstrap-4') }}
</div>

<script>
function filterTable() {
    // Ambil input dan filter
    let input = document.getElementById("searchInput");
    let filter = input.value.toUpperCase();
    let table = document.getElementById("laporanTable");
    let tr = table.getElementsByTagName("tr");

    // Loop semua baris tabel (lewati header di index 0)
    for (let i = 1; i < tr.length; i++) {
        let tdKode = tr[i].getElementsByTagName("td")[1]; // Kolom Kode
        let tdNama = tr[i].getElementsByTagName("td")[2]; // Kolom Nasabah
        
        if (tdKode || tdNama) {
            let txtValueKode = tdKode.textContent || tdKode.innerText;
            let txtValueNama = tdNama.textContent || tdNama.innerText;
            
            // Cek apakah ada kecocokan di salah satu kolom
            if (txtValueKode.toUpperCase().indexOf(filter) > -1 || 
                txtValueNama.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}
</script>