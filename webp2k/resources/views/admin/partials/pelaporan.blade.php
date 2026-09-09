{{-- JUDUL DAN BREADCRUMB --}}
<div class="page-title" style="margin-bottom: 25px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #000; margin-bottom: 5px;">Pelaporan</h2>
    <p style="font-size: 14px; font-weight: 600;">
        <span onclick="window.location.href='/admin/dashboard'" style="cursor:pointer; color:#4e4bc1;">Dashboard</span> 
        <span style="margin: 0 5px;">></span> 
        <span style="color: #007bff;">Pelaporan</span>
    </p>
</div>

{{-- TOMBOL EXPORT DAN SEARCH --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <button onclick="openModalExportPelaporan()" style="background-color: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: bold; display: flex; align-items: center; gap: 8px; cursor: pointer;">
        <i class="fa-solid fa-file-excel"></i> Export Excel
    </button>
    <input type="text" 
        id="searchInput" 
        placeholder="Pencarian.." 
        class="search-input" 
        style="padding: 10px 15px; border-radius: 20px; border: 1px solid #ddd; width: 250px;">
</div>

@if(!request()->ajax())
<div id="isi-tabel-pelaporan">
@endif

    {{-- TABEL 1: DAFTAR AO --}}
    <div style="margin-bottom: 15px;">
        <h3 style="font-size: 18px; font-weight: 700; color: #333;">Daftar AO Aktif Berkunjung</h3>
    </div>
    <div class="table-responsive" style="margin-bottom: 40px;">
        <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; background-color: #fff;">
            <thead>
                <tr style="border-bottom: 2px solid #000; text-align: center; background-color: #fcfcfc;">
                    <th style="padding: 15px; border-right: 2px solid #000; width: 60px;">No</th>
                    <th style="padding: 15px; border-right: 2px solid #000; width: 180px;">Tanggal Kunjungan Terakhir</th>
                    <th style="padding: 15px; border-right: 2px solid #000; width: 150px;">Kode AO</th>
                    <th style="padding: 15px;">Nama AO</th>
                </tr>
            </thead>
            <tbody style="font-weight: 700; font-size: 14px; text-align: center;">
                @forelse($pelaporan_all as $index => $item)
                <tr style="border-bottom: 2px solid #000;">
                    <td style="padding: 12px; border-right: 2px solid #000;">{{ $index + 1 }}</td>
                    <td style="padding: 12px; border-right: 2px solid #000;">
                        {{-- Menggunakan created_at dari hasil realisasi/laporan --}}
                        {{ $item->kunjungan_terbaru ? \Carbon\Carbon::parse($item->kunjungan_terbaru->created_at)->format('d-m-Y') : '-' }}
                    </td>
                    <td style="padding: 12px; border-right: 2px solid #000;">{{ $item->kode_ao }}</td>
                    <td style="padding: 12px; text-align: left; padding-left: 20px;">
                        <span onclick="loadAdminPage('pelaporan-detail/{{ $item->id }}')" 
                              style="cursor: pointer; color: #000; text-decoration: underline;">
                            {{ $item->nama ?? 'Nama Tidak Ada' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="padding: 30px; text-align: center; color: #888;">Tidak ada AO yang melakukan kunjungan.</td></tr>
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
                    <th style="padding: 15px; border-right: 2px solid #000;">Tgl Kunjung</th>
                    <th style="padding: 15px; width: 80px;">Aksi</th>
                </tr>
            </thead>
            <tbody style="font-weight: 700; font-size: 14px; text-align: center;">
                @forelse($nasabah_terkunjungi as $index => $nasabah)
                <tr style="border-bottom: 2px solid #000;">
                    <td style="padding: 12px; border-right: 2px solid #000;">{{ $nasabah_terkunjungi->firstItem() + $index }}</td>
                    <td style="padding: 12px; border-right: 2px solid #000;">{{ $nasabah->no_angsuran }}</td>
                    <td style="padding: 12px; border-right: 2px solid #000; text-align: left; padding-left: 20px; text-transform: uppercase;">
                        {{ $nasabah->nasabah }}
                    </td>
                    <td style="padding: 12px; border-right: 2px solid #000; text-align: left; padding-left: 10px;">
                        @if($nasabah->laporanSelesai->isNotEmpty())
                            @foreach($nasabah->laporanSelesai as $kunj)
                                <div style="margin-bottom: 4px;">
                                    <i class="fa-solid fa-user-check" style="color: #28a745; font-size: 12px;"></i> 
                                    ({{ $kunj->kode_ao }}) {{ $kunj->karyawan->nama ?? 'Tanpa Nama' }}
                                </div>
                            @endforeach
                        @else 
                            <span style="color: #ccc; font-weight: 400;">Belum dikunjungi</span> 
                        @endif
                    </td>

                    <td style="padding: 12px; text-align: left; padding-left: 10px;">
                        @if($nasabah->laporanSelesai->isNotEmpty())
                            @foreach($nasabah->laporanSelesai as $kunj)
                                <div style="margin-bottom: 4px; color: #666;">
                                    {{ \Carbon\Carbon::parse($kunj->created_at)->format('d-m-Y') }}
                                </div>
                            @endforeach
                        @else 
                            - 
                        @endif
                    </td>
                    <td style="padding: 12px;">
                        @if($nasabah->laporanSelesai->isNotEmpty())
                            <button onclick="lihatDetailNasabah('{{ $nasabah->no_angsuran }}', '{{ addslashes($nasabah->nasabah) }}')" 
                                    title="Detail Laporan"
                                    style="background: #3f36b1; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                <i class="fa-solid fa-eye" style="font-size: 11px;"></i> Detail
                            </button>
                        @else 
                            - 
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="padding: 30px; text-align: center; color: #888;">Belum ada data nasabah yang berhasil dikunjungi.</td></tr>
                @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px; display: flex; justify-content: center;">
        {{ $nasabah_terkunjungi->links() }}
    </div>

</div>

@if(!request()->ajax())
</div>
@endif

<div id="modalDetailNasabah" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 10000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; width: 100%; max-width: 800px; max-height: 90vh; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.3); display: flex; flex-direction: column;">
        <div style="padding: 15px 20px; background: #3f36b1; color: white; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
            <h3 id="detailNasabahTitle" style="margin: 0; font-weight: 800; font-size: 16px;">Detail Laporan Kunjungan</h3>
            <button onclick="closeModalDetailNasabah()" style="background: none; border: none; color: white; font-size: 22px; cursor: pointer;">&times;</button>
        </div>
        <div id="detailNasabahBody" style="padding: 20px; overflow-y: auto; flex: 1;">
            <div style="text-align: center; padding: 30px; color: #888;">
                <i class="fas fa-spinner fa-spin"></i> Memuat data...
            </div>
        </div>
    </div>
</div>

<script>
function lihatDetailNasabah(noAngsuran, namaNasabah) {
    document.getElementById('detailNasabahTitle').textContent = 'Laporan Kunjungan — ' + namaNasabah;
    document.getElementById('detailNasabahBody').innerHTML = '<div style="text-align:center;padding:30px;color:#888;"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>';
    document.getElementById('modalDetailNasabah').style.display = 'flex';

    fetch('/admin/pelaporan/laporan-nasabah/' + noAngsuran, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.text().then(t => { let d; try { d = JSON.parse(t); } catch(e) { d = null; } return d; }))
    .then(data => {
        if (!data || !data.laporan) {
            document.getElementById('detailNasabahBody').innerHTML = '<div style="text-align:center;padding:30px;color:#e74c3c;">Gagal memuat data.</div>';
            return;
        }
        renderDetailNasabah(data);
    })
    .catch(() => {
        document.getElementById('detailNasabahBody').innerHTML = '<div style="text-align:center;padding:30px;color:#e74c3c;">Gagal memuat data.</div>';
    });
}

function renderDetailNasabah(data) {
    const n = data.nasabah;
    const laporan = data.laporan;
    let html = '';

    html += '<div style="background:#f8f9fa;border-radius:10px;padding:15px;margin-bottom:15px;border-left:4px solid #3f36b1;">';
    html += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:13px;">';
    html += '<div><strong>No. Angsuran:</strong> ' + (n.no_angsuran || '-') + '</div>';
    html += '<div><strong>Nama:</strong> ' + (n.nama || '-') + '</div>';
    html += '<div><strong>Alamat:</strong> ' + (n.alamat || '-') + '</div>';
    html += '<div><strong>KOL:</strong> ' + (n.kol || '-') + '</div>';
    html += '</div></div>';

    if (laporan.length === 0) {
        html += '<div style="text-align:center;padding:30px;color:#888;">Belum ada laporan kunjungan.</div>';
    } else {
        laporan.forEach(function(l, i) {
            html += '<div style="border:1px solid #e0e0e0;border-radius:10px;padding:15px;margin-bottom:12px;">';
            html += '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">';
            html += '<div style="font-weight:700;color:#3f36b1;font-size:14px;">Kunjungan #' + (i + 1) + '</div>';
            html += '<div style="font-size:12px;color:#888;">' + l.created_at + '</div>';
            html += '</div>';

            html += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;font-size:13px;margin-bottom:8px;">';
            html += '<div><strong>AO:</strong> (' + l.kode_ao + ') ' + l.nama_ao + '</div>';
            html += '<div><strong>Ada di Lokasi:</strong> ' + l.ada_di_lokasi + '</div>';
            html += '<div><strong>Tgl Janji Bayar:</strong> ' + l.tgl_janji_bayar + '</div>';
            html += '<div><strong>Nominal Janji:</strong> ' + l.nominal_janji_bayar + '</div>';
            html += '</div>';

            if (l.catatan && l.catatan !== '-') {
                html += '<div style="margin-bottom:8px;"><strong style="font-size:13px;">Catatan:</strong><div style="background:#f8f9fa;padding:8px;border-radius:6px;font-size:13px;margin-top:4px;">' + l.catatan + '</div></div>';
            }

            if (l.foto && l.foto.length > 0) {
                html += '<div style="margin-top:8px;"><strong style="font-size:13px;">Foto:</strong><div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:6px;">';
                l.foto.forEach(function(f) {
                    html += '<a href="/uploads/kunjungan/' + f + '" target="_blank"><img src="/uploads/kunjungan/' + f + '" style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid #ddd;"></a>';
                });
                html += '</div></div>';
            }

            if (l.bukti_transfer) {
                html += '<div style="margin-top:8px;"><strong style="font-size:13px;">Bukti Transfer:</strong><div style="margin-top:4px;"><a href="/uploads/kunjungan/' + l.bukti_transfer + '" target="_blank"><img src="/uploads/kunjungan/' + l.bukti_transfer + '" style="max-width:150px;border-radius:8px;border:1px solid #ddd;"></a></div></div>';
            }

            html += '</div>';
        });
    }

    document.getElementById('detailNasabahBody').innerHTML = html;
}

function closeModalDetailNasabah() {
    document.getElementById('modalDetailNasabah').style.display = 'none';
}

document.getElementById('modalDetailNasabah').addEventListener('click', function(e) {
    if (e.target === this) closeModalDetailNasabah();
});
</script>