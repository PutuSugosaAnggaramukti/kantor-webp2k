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

        <button onclick="openModalImportHB()" style="background-color: #d9534f; color: white; border: none; padding: 8px 15px; border-radius: 10px; font-weight: 700; display: flex; align-items: center; cursor: pointer; font-size: 13px;">
            <span style="margin-right: 5px;"><i class="fa-solid fa-file-circle-exclamation"></i></span> Import HB
        </button>

        <button onclick="openModalImportNasabah()" style="background-color: #2196F3; color: white; border: none; padding: 8px 15px; border-radius: 10px; font-weight: 700; display: flex; align-items: center; cursor: pointer; font-size: 13px;">
            <span style="margin-right: 5px;"><i class="fa-solid fa-file-import"></i></span> Import Data
        </button>

        <button onclick="openModalTambahNasabah()" style="background-color: #4e4bc1; color: white; border: none; padding: 8px 15px; border-radius: 10px; font-weight: 700; display: flex; align-items: center; cursor: pointer; font-size: 13px;">
            <span style="margin-right: 5px;"><i class="fa fa-plus"></i></span> Tambah Nasabah
        </button>
    </div>
    
    <input type="text" 
       id="searchInput" 
       placeholder="Cari nama atau no. angsuran.." 
       class="search-input" 
       style="padding: 10px 15px; border-radius: 20px; border: 1px solid #ddd; width: 230px;"
       value="{{ request('search') }}">
       
</div>

<div style="display: flex; gap: 5px; margin-bottom: -2px; position: relative; z-index: 1; flex-wrap: wrap;">

   <div style="margin-bottom: -2px; display: flex; gap: 5px; flex-wrap: wrap;">
        <button onclick="switchTab('1')" style="padding: 10px 15px; border: 2px solid #000; border-bottom: {{ $activeTab == '1' ? 'none' : '2px solid #000' }}; border-radius: 10px 10px 0 0; background-color: {{ $activeTab == '1' ? '#fff' : '#eee' }}; font-weight: 800; color: #28a745; cursor: pointer;">
            Lancar ({{ $count1 ?? 0 }})
        </button>
        <button onclick="switchTab('2')" style="padding: 10px 15px; border: 2px solid #000; border-bottom: {{ $activeTab == '2' ? 'none' : '2px solid #000' }}; border-radius: 10px 10px 0 0; background-color: {{ $activeTab == '2' ? '#fff' : '#eee' }}; font-weight: 800; color: #ffc107; cursor: pointer;">
            DPK ({{ $count2 ?? 0 }})
        </button>
        <button onclick="switchTab('3')" style="padding: 10px 15px; border: 2px solid #000; border-bottom: {{ $activeTab == '3' ? 'none' : '2px solid #000' }}; border-radius: 10px 10px 0 0; background-color: {{ $activeTab == '3' ? '#fff' : '#eee' }}; font-weight: 800; color: #fd7e14; cursor: pointer;">
            Kurang Lancar ({{ $count3 ?? 0 }})
        </button>
        <button onclick="switchTab('4')" style="padding: 10px 15px; border: 2px solid #000; border-bottom: {{ $activeTab == '4' ? 'none' : '2px solid #000' }}; border-radius: 10px 10px 0 0; background-color: {{ $activeTab == '4' ? '#fff' : '#eee' }}; font-weight: 800; color: #dc3545; cursor: pointer;">
            Diragukan ({{ $count4 ?? 0 }})
        </button>

        <button onclick="switchTab('5')" 
            style="padding: 10px 15px; border: 2px solid #000; border-bottom: {{ $activeTab == '5' ? 'none' : '2px solid #000' }}; border-radius: 10px 10px 0 0; background-color: {{ $activeTab == '5' ? '#fff' : '#eee' }}; font-weight: 800; color: #721c24; cursor: pointer;">
            Macet ({{ $count5 ?? 0 }})
        </button>

        <button onclick="switchTab('hb')" 
            style="padding: 10px 15px; border: 2px solid #000; border-bottom: {{ $activeTab == 'hb' ? 'none' : '2px solid #000' }}; border-radius: 10px 10px 0 0; background-color: {{ $activeTab == 'hb' ? '#fff' : '#eee' }}; font-weight: 800; color: #000; cursor: pointer;">
            HB 
            <span style="background: #333; color: white; padding: 2px 8px; border-radius: 20px; font-size: 11px; margin-left: 5px;">
                {{ $countHB ?? 0 }}
            </span>
        </button>
    </div>
</div>

<div id="main-content-area" style="border: 2px solid #000; background: #fff; padding: 0px; border-radius: 0 0 10px 10px; overflow: hidden; transition: opacity 0.3s;">
   <div class="table-responsive" style="overflow-x: auto;">
        <table class="table table-bordered table-hover" style="min-width: 2000px; font-size: 12px;">
           <thead class="bg-light text-center">
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>No. Anggota</th>
                    <th>Rekening Kredit</th> 
                    <th>Kode Nasabah</th>    
                    <th>Nama Nasabah</th>    
                    <th style="min-width: 250px;">Alamat</th>
                    <th>Tgl Pinjam</th>
                    <th>Tgl JT</th>
                    <th>Nominal</th>
                    <th>Sisa Pokok</th>
                    <th>Pokok/Bln</th>
                    <th>Bunga/Bln</th>
                    <th>Tunggakan Pokok</th>
                    <th>Hari</th>
                    <th>Tunggakan Bunga</th>
                    <th>Hari</th>
                    <th>Denda</th>
                    <th>Total Tunggakan</th>
                    <th>Agunan</th>
                    <th>Ikatan</th>
                    <th>KOL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($nasabah_all as $index => $row)
                <tr>
                    <td class="text-center">{{ $nasabah_all->firstItem() + $index }}</td>
                    <td class="text-center">{{ $row->kode }}</td>
                    <td>{{ $row->no_angsuran }}</td>
                    <td>{{ $row->rekening_kredit }}</td> 
                    <td>{{ $row->kode_nasabah }}</td>    
                    <td>{{ $row->nasabah }}</td>         
                    <td><small>{{ $row->alamat }}</small></td>
                    <td class="text-center">{{ $row->tgl_pinjam ?? '-' }}</td>
                    <td class="text-center">{{ $row->tgl_jt ?? '-' }}</td>
                    <td class="text-end">{{ number_format($row->nominal, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($row->sisa_pokok, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($row->pokok_per_bulan, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($row->bunga_per_bulan, 0, ',', '.') }}</td>
                    <td class="text-end text-danger">{{ number_format($row->tunggakan_pokok, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $row->hari_pokok }}</td>
                    <td class="text-end text-danger">{{ number_format($row->tunggakan_bunga, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $row->hari_bunga }}</td>
                    <td class="text-end">{{ number_format($row->denda, 0, ',', '.') }}</td>
                    <td class="text-end"><strong>{{ number_format($row->bakidebet, 0, ',', '.') }}</strong></td>
                    
                    <td class="text-center">{{ $row->kode_agunan ?? '-' }}</td>
                    <td class="text-center">{{ $row->ikatan ?? '-' }}</td>

                    <td class="text-center">
                        <span class="badge bg-{{ $row->kol == 1 ? 'success' : ($row->kol >= 4 ? 'danger' : 'warning') }}">
                            {{ $row->kol }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper" style="padding: 15px; border-top: 2px solid #000;">
        {{ $nasabah_all->links() }}
    </div>
</div>