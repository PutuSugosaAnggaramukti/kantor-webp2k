<div class="page-title" style="margin-bottom: 25px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #000; margin-bottom: 5px;">Data Nasabah</h2>
    <p style="font-size: 14px; font-weight: 600;">
        <span onclick="window.location.href='/admin/dashboard'" style="cursor:pointer; color:#4e4bc1;">Dashboard</span> 
        <span style="margin: 0 5px;">></span> 
        <span style="color: #007bff;">Data Nasabah</span>
    </p>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div style="display: flex; gap: 10px;">
        <button onclick="openModalExportNasabah()" style="background-color: #4CAF50; color: white; border: none; padding: 8px 20px; border-radius: 12px; font-weight: 700; display: flex; align-items: center; cursor: pointer;">
            <span style="margin-right: 8px;"><i class="fa-solid fa-file-excel"></i></span> Export
        </button>

        <button onclick="openModalImportNasabah()" style="background-color: #2196F3; color: white; border: none; padding: 8px 20px; border-radius: 12px; font-weight: 700; display: flex; align-items: center; cursor: pointer;">
            <span style="margin-right: 8px;"><i class="fa-solid fa-file-import"></i></span> Import Data
        </button>

        <button onclick="openModalFilter()" style="background-color: #ff9800; color: white; border: none; padding: 8px 20px; border-radius: 12px; font-weight: 700; display: flex; align-items: center; cursor: pointer;">
            <span style="margin-right: 8px;"><i class="fa-solid fa-sliders"></i></span> Filter Data
        </button>

        <button onclick="openModalTambahNasabah()" style="background-color: #4e4bc1; color: white; border: none; padding: 8px 20px; border-radius: 12px; font-weight: 700; display: flex; align-items: center; cursor: pointer;">
            <span style="margin-right: 8px;"><i class="fa fa-plus"></i></span> Tambah Nasabah
        </button>
    </div>
    
    <input type="text" placeholder="Cari nama atau no. angsuran.." class="search-input" style="padding: 10px 15px; border-radius: 20px; border: 1px solid #ddd; width: 250px;">
</div>


<div id="container-nasabah">
    <div class="table-responsive">
       <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; background-color: #fff;">
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
                <tr style="border-bottom: 2px solid #000; font-weight: 600; font-size: 13px;">
                    <td style="padding: 10px; border-right: 2px solid #000; text-align: center;">
                        {{ ($nasabah_all->currentPage() - 1) * $nasabah_all->perPage() + $loop->iteration }}
                    </td>
                    
                    <td style="padding: 10px; border-right: 2px solid #000; text-align: center;">
                        {{ $nasabah->no_angsuran }}
                    </td>
                    
                    <td style="padding: 10px; border-right: 2px solid #000; text-align: left; text-transform: uppercase;">
                        <b>{{ $nasabah->nasabah }}</b>
                    </td>

                    <td style="padding: 10px; border-right: 2px solid #000; text-align: left; font-size: 12px;">
                        {{ $nasabah->alamat }}
                    </td>

                    {{-- Kolom Nominal / Plafond --}}
                    <td style="padding: 10px; border-right: 2px solid #000; text-align: right; color: #2b579a;">
                        {{ number_format($nasabah->nominal ?? 0, 0, ',', '.') }}
                    </td>

                    {{-- Kolom Sisa Pokok (OS) --}}
                    <td style="padding: 10px; border-right: 2px solid #000; text-align: right; color: #d9534f; font-weight: 800;">
                        {{ number_format($nasabah->sisa_pokok ?? 0, 0, ',', '.') }}
                    </td>
                    
                    <td style="padding: 10px; text-align: center;">
                        <span style="padding: 4px 10px; border-radius: 5px; background: {{ $nasabah->kol == 5 ? '#ff4d4d' : '#eee' }}; color: {{ $nasabah->kol == 5 ? '#fff' : '#000' }};">
                            {{ $nasabah->kol }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding: 30px; text-align: center; font-weight: bold; color: #888;">
                        Belum ada data nasabah.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div style="margin-top: 20px;">
            {{ $nasabah_all->links() }}
        </div>
    </div>
</div>