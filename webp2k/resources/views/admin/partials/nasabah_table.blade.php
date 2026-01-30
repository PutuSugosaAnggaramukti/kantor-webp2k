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
            <span style="margin-right: 8px;">X</span> Export
        </button>
       <button onclick="openModalFilter()" style="background-color: #4CAF50; color: white; border: none; padding: 8px 20px; border-radius: 12px; font-weight: 700; display: flex; align-items: center; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#45a049'; this.style.transform='translateY(-2px)'" onmouseout="this.style.backgroundColor='#4CAF50'; this.style.transform='translateY(0)'">
            <span style="margin-right: 8px;">
                <i class="fa-solid fa-sliders"></i>
            </span> 
            Filter Data
        </button>
        <button onclick="openModalTambahNasabah()" style="background-color: #4CAF50; color: white; border: none; padding: 8px 20px; border-radius: 12px; font-weight: 700; display: flex; align-items: center; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#45a049'; this.style.transform='translateY(-2px)'" onmouseout="this.style.backgroundColor='#4CAF50'; this.style.transform='translateY(0)'">
            <span style="margin-right: 8px;">
                <i class="fa fa-plus"></i>
            </span> Tambah Nasabah
        </button>
    </div>
    
    <input type="text" placeholder="Pencarian.." class="search-input" style="padding: 10px 15px; border-radius: 20px; border: 1px solid #ddd; width: 250px;">
</div>

<div id="container-nasabah">
    <div class="table-responsive">
       <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; background-color: #fff;">
            <thead>
                <tr style="border-bottom: 2px solid #000; text-align: center; background-color: #fcfcfc;">
                    <th style="padding: 15px; border-right: 2px solid #000; width: 60px;">No</th>
                    <th style="padding: 15px; border-right: 2px solid #000; width: 120px;">No. Ang</th>
                    <th style="padding: 15px; border-right: 2px solid #000;">Nama</th>
                    <th style="padding: 15px; border-right: 2px solid #000;">Alamat</th>
                    <th style="padding: 15px; width: 100px;">KOL</th>
                </tr>
            </thead>
            <tbody id="isi-tabel-nasabah">
                @forelse($nasabah_all as $nasabah)
                <tr style="border-bottom: 2px solid #000; font-weight: 700; text-align: center;">
                    <td style="padding: 15px; border-right: 2px solid #000;">{{ $loop->iteration }}</td>
                    
                    <td style="padding: 15px; border-right: 2px solid #000;">
                        {{ $nasabah->no_angsuran }}
                    </td>
                    
                    <td style="padding: 15px; border-right: 2px solid #000; text-align: left; padding-left: 20px;">
                        {{ $nasabah->nasabah }} {{-- Sesuaikan jika kolom di DB namanya 'nasabah' --}}
                    </td>

                    <td style="padding: 15px; border-right: 2px solid #000; text-align: left; padding-left: 20px;">
                        {{ $nasabah->alamat }}
                    </td>
                    
                    <td style="padding: 15px;">
                        {{ $nasabah->kol }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding: 30px; text-align: center; font-weight: bold; color: #888;">
                        Belum ada data nasabah.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
