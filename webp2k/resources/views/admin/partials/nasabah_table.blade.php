<div class="page-title" style="margin-bottom: 25px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #000; margin-bottom: 5px;">Data Nasabah</h2>
    <p style="font-size: 14px; font-weight: 600;">
        <span onclick="window.location.href='/admin/dashboard'" style="cursor:pointer; color:#4e4bc1;">Dashboard</span> 
        <span style="margin: 0 5px;">></span> 
        <span style="color: #007bff;">Data Nasabah</span>
    </p>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <button onclick="openModalExportNasabah()" style="background-color: #4CAF50; color: white; border: none; padding: 8px 15px; border-radius: 10px; font-weight: 700; display: flex; align-items: center; cursor: pointer; font-size: 13px;">
            <span style="margin-right: 5px;"><i class="fa-solid fa-file-excel"></i></span> Export
        </button>

        <button onclick="openModalImportNasabah()" style="background-color: #2196F3; color: white; border: none; padding: 8px 15px; border-radius: 10px; font-weight: 700; display: flex; align-items: center; cursor: pointer; font-size: 13px;">
            <span style="margin-right: 5px;"><i class="fa-solid fa-file-import"></i></span> Import Data
        </button>

        <button onclick="openModalImportHB()" style="background-color: #d9534f; color: white; border: none; padding: 8px 15px; border-radius: 10px; font-weight: 700; display: flex; align-items: center; cursor: pointer; font-size: 13px;">
            <span style="margin-right: 5px;"><i class="fa-solid fa-file-circle-exclamation"></i></span> Import Data HB
        </button>

        <button onclick="openModalTambahNasabah()" style="background-color: #4e4bc1; color: white; border: none; padding: 8px 15px; border-radius: 10px; font-weight: 700; display: flex; align-items: center; cursor: pointer; font-size: 13px;">
            <span style="margin-right: 5px;"><i class="fa fa-plus"></i></span> Tambah Nasabah
        </button>
    </div>
    
    <input type="text" placeholder="Cari nama atau no. angsuran.." class="search-input" style="padding: 10px 15px; border-radius: 20px; border: 1px solid #ddd; width: 230px;">
</div>

<div style="display: flex; gap: 5px; margin-bottom: -2px; position: relative; z-index: 1;">
   <button onclick="switchTab('all')" id="btn-tab-all" 
        style="padding: 10px 25px; border: 2px solid #000; border-bottom: none; border-radius: 10px 10px 0 0; background-color: #fff; font-weight: 800; cursor: pointer; transition: 0.3s; display: flex; align-items: center;">
        Semua Nasabah
        <span style="background: #4e4bc1; color: white; padding: 2px 8px; border-radius: 20px; font-size: 11px; margin-left: 8px; box-shadow: inset 0 0 5px rgba(0,0,0,0.2);">
            {{ $countReguler }}
        </span>
    </button>
    <button onclick="switchTab('hb')" id="btn-tab-hb" style="padding: 10px 25px; border: 2px solid #000; border-bottom: 2px solid #000; border-radius: 10px 10px 0 0; background-color: #eee; font-weight: 800; color: #d9534f; cursor: pointer; transition: 0.3s;">
        Nasabah HB (KOL 5) <span style="background: #d9534f; color: white; padding: 2px 8px; border-radius: 20px; font-size: 11px; margin-left: 5px;">{{ $nasabah_hb->count() }}</span>
    </button>
</div>

<div id="container-nasabah" style="border: 2px solid #000; background: #fff; padding: 0px; border-radius: 0 0 10px 10px; overflow: hidden;">
    
    <div id="tab-all-content">
        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #000; text-align: center; background-color: #f0f0f0;">
                        <th style="padding: 12px; border-right: 2px solid #000; width: 50px;">No</th>
                        <th style="padding: 12px; border-right: 2px solid #000; width: 110px;">No. Ang</th>
                        <th style="padding: 12px; border-right: 2px solid #000;">Nama Nasabah</th>
                        <th style="padding: 12px; border-right: 2px solid #000;">Alamat</th>
                        <th style="padding: 12px; border-right: 2px solid #000; width: 120px;">Nominal</th>
                        <th style="padding: 12px; border-right: 2px solid #000; width: 120px;">Sisa Pokok</th>
                        <th style="padding: 12px; width: 60px;">KOL</th>
                    </tr>
                </thead>
                <tbody id="isi-tabel-nasabah">
                    @forelse($nasabah_all as $nasabah)
                    <tr style="border-bottom: 1px solid #ddd; font-weight: 600; font-size: 13px;">
                        <td style="padding: 10px; border-right: 2px solid #000; text-align: center;">{{ ($nasabah_all->currentPage() - 1) * $nasabah_all->perPage() + $loop->iteration }}</td>
                        <td style="padding: 10px; border-right: 2px solid #000; text-align: center;">{{ $nasabah->no_angsuran }}</td>
                        <td style="padding: 10px; border-right: 2px solid #000; text-transform: uppercase;"><b>{{ $nasabah->nasabah }}</b></td>
                        <td style="padding: 10px; border-right: 2px solid #000; font-size: 12px;">{{ $nasabah->alamat ?? '-' }}</td>
                        <td style="padding: 10px; border-right: 2px solid #000; text-align: right; color: #2b579a;">{{ number_format($nasabah->nominal ?? 0, 0, ',', '.') }}</td>
                        <td style="padding: 10px; border-right: 2px solid #000; text-align: right; color: #d9534f;">{{ number_format($nasabah->sisa_pokok ?? 0, 0, ',', '.') }}</td>
                        <td style="padding: 10px; text-align: center;">
                            <span style="padding: 4px 10px; border-radius: 5px; background: {{ $nasabah->kol == 5 ? '#ff4d4d' : '#eee' }}; color: {{ $nasabah->kol == 5 ? '#fff' : '#000' }};">{{ $nasabah->kol }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="padding: 30px; text-align: center; font-weight: bold; color: #888;">Belum ada data nasabah.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrapper" style="padding: 15px; border-top: 2px solid #000;">
            {{ $nasabah_all->links() }}
        </div>
    </div>

   <div id="tab-hb-content" style="display: none;">
        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #000; text-align: center; background-color: #ffebeb;">
                        <th style="padding: 12px; border-right: 2px solid #000; width: 50px;">No</th>
                        <th style="padding: 12px; border-right: 2px solid #000; width: 110px;">No. Ang</th>
                        <th style="padding: 12px; border-right: 2px solid #000;">Nama Nasabah</th>
                        <th style="padding: 12px; border-right: 2px solid #000;">Alamat</th>
                        <th style="padding: 12px; width: 120px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nasabah_hb as $hb)
                    <tr style="border-bottom: 1px solid #ddd; font-weight: 600; font-size: 13px;">
                        <td style="padding: 10px; border-right: 2px solid #000; text-align: center;">{{ $loop->iteration }}</td>
                        <td style="padding: 10px; border-right: 2px solid #000; text-align: center;">{{ $hb->no_angsuran }}</td>
                        <td style="padding: 10px; border-right: 2px solid #000; text-transform: uppercase; color: #d9534f;"><b>{{ $hb->nasabah }}</b></td>
                        <td style="padding: 10px; border-right: 2px solid #000; font-size: 12px;">{{ $hb->alamat ?? '-' }}</td>
                        <td style="padding: 10px; text-align: center;">
                            <span style="padding: 6px 15px; border-radius: 20px; background: #d9534f; color: #fff; font-size: 11px; font-weight: 800; border: 1px solid #000;">
                               HB
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="padding: 30px; text-align: center; font-weight: bold; color: #888;">Tidak ada data nasabah Hapus Buku.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function switchTab(type) {
    const allTab = document.getElementById('tab-all-content');
    const hbTab = document.getElementById('tab-hb-content');
    const btnAll = document.getElementById('btn-tab-all');
    const btnHb = document.getElementById('btn-tab-hb');

    if (type === 'all') {
        allTab.style.display = 'block';
        hbTab.style.display = 'none';
        btnAll.style.backgroundColor = '#fff';
        btnAll.style.borderBottom = 'none';
        btnHb.style.backgroundColor = '#eee';
        btnHb.style.borderBottom = '2px solid #000';
    } else {
        allTab.style.display = 'none';
        hbTab.style.display = 'block';
        btnHb.style.backgroundColor = '#fff';
        btnHb.style.borderBottom = 'none';
        btnAll.style.backgroundColor = '#eee';
        btnAll.style.borderBottom = '2px solid #000';
    }
}

function openModalImportHB() {
    const modal = document.getElementById('modalImportHB');
    modal.style.display = 'flex'; // Gunakan 'flex', bukan 'block'
}

function closeModalImportHB() {
    const modal = document.getElementById('modalImportHB');
    modal.style.display = 'none';
}
</script>