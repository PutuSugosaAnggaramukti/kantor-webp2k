<div class="page-title" style="margin-bottom: 25px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #000; margin-bottom: 5px;">Rekap Data Kunjungan</h2>
    <p style="font-size: 14px; font-weight: 600;">
        <span onclick="window.location.href='/admin/dashboard'" style="cursor:pointer; color:#4e4bc1;">Dashboard</span> 
        <span style="margin: 0 5px;">></span> 
        <span style="color: #007bff;">Data Kunjungan</span>
    </p>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <a href="{{ route('admin.kunjungan.export') }}" class="btn-export-excel">
        <span style="margin-right: 8px;">
            <i class="fa-solid fa-file-excel"></i>
        </span> 
        Export
    </a>
        
    <div style="position: relative;">
        <input type="text" placeholder="Pencarian..." style="padding: 8px 15px; border-radius: 20px; border: 1px solid #ccc; width: 250px;">
    </div>
</div>

<div class="table-responsive">
    <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; background-color: #fff;">
        <thead>
            <tr style="border-bottom: 2px solid #000; text-align: center; background-color: #fcfcfc;">
                <th style="padding: 15px; border-right: 2px solid #000; width: 60px;">No</th>
                <th style="padding: 15px; border-right: 2px solid #000; width: 120px;">Kode AO</th>
                <th style="padding: 15px; border-right: 2px solid #000;">Nama</th>
                <th style="padding: 15px; width: 180px;">Jumlah Kunjungan</th>
            </tr>
        </thead>
        <tbody style="font-weight: 700; font-size: 14px;">
            @foreach($karyawan as $index => $item)
            <tr style="border-bottom: 2px solid #000; text-align: center;">
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $index + 1 }}</td>
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $item->kode_ao }}</td>
                <td style="padding: 15px; border-right: 2px solid #000; text-align: left; padding-left: 20px;">
                    {{ $item->nama }}
                </td>
                <td style="padding: 15px; text-align: center;">
                    <span 
                        onclick="loadAdminPage('kunjungan-detail/{{ $item->kode_ao }}')" 
                        style="color: #007bff; text-decoration: underline; cursor: pointer; font-weight: 800;">
                        {{ $item->kunjungan_count }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div id="modalTambahNasabah" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
    
    <div class="modal-dialog modal-dialog-centered" role="document" style="width: 100%; max-width: 500px;"> 
        <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            
            <div class="modal-header" style="background-color: #4CAF50; color: white; border-top-left-radius: 15px; border-top-right-radius: 15px; display: flex; justify-content: space-between; align-items: center; padding: 15px 20px;">
                <h5 class="modal-title" style="font-weight: 700; margin: 0;">
                    <i class="fa fa-user-plus" style="margin-right: 10px;"></i>Input Nasabah Baru
                </h5>
                <button type="button" onclick="closeModalTambahNasabah()" style="background: none; border: none; color: white; font-size: 24px; cursor: pointer;">&times;</button>
            </div>

            <form action="{{ route('nasabah.store') }}" method="POST" id="formTambahNasabah">
                @csrf
                <div class="modal-body" style="padding: 25px;">
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="font-weight: 700; color: #333;">No. Anggota</label>
                        <input type="text" name="no_angsuran" class="form-control" style="border-radius: 8px;" placeholder="Contoh: 821.22.0001" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="font-weight: 700; color: #333;">Nama Nasabah</label>
                        <input type="text" name="nama_nasabah" class="form-control" style="border-radius: 8px;" placeholder="Masukkan nama lengkap" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="font-weight: 700; color: #333;">Alamat</label>
                        <textarea name="alamat_nasabah" class="form-control" rows="3" style="border-radius: 8px;" placeholder="Alamat domisili nasabah" required></textarea>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 700; color: #333;">KOL (Kolektibilitas)</label>
                        <select name="kol" class="form-control" style="border-radius: 8px;" required>
                            <option value="" disabled selected>-- Pilih Status KOL --</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px; background-color: #f8f9fa; display: flex; justify-content: flex-end; gap: 10px; padding: 15px;">
                    <button type="button" onclick="closeModalTambahNasabah()" class="btn btn-secondary" style="border-radius: 8px; font-weight: 600; padding: 8px 20px;">Batal</button>
                    <button type="submit" class="btn btn-success" style="border-radius: 8px; font-weight: 600; background-color: #4CAF50; border: none; padding: 8px 25px; color: white; cursor: pointer;">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>