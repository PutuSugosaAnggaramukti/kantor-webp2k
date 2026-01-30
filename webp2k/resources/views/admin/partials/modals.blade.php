<div id="modalTambahKaryawan" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
    
    <div style="background-color: #fff; padding: 30px; border-radius: 20px; width: 450px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); position: relative;">
        
        <h2 style="text-align: center; font-size: 24px; font-weight: 800; margin-bottom: 25px;">Tambah Karyawan</h2>

        <form id="formTambahKaryawan">
            <div style="margin-bottom: 15px;">
                <label>Kode AO*</label>
                <input type="text" name="kode_ao" required style="width: 100%; padding: 10px; border: 1px solid #000; border-radius: 5px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 15px;">
                <label>Nama*</label>
                <input type="text" name="nama" required style="width: 100%; padding: 10px; border: 1px solid #000; border-radius: 5px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 15px;">
                <label>Username*</label>
                <input type="text" name="username" required style="width: 100%; padding: 10px; border: 1px solid #000; border-radius: 5px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 15px;">
                <label>Password*</label>
                <input type="password" name="password" required style="width: 100%; padding: 10px; border: 1px solid #000; border-radius: 5px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 30px;">
                <label>Status*</label>
                <select name="status" style="width: 100%; padding: 10px; border: 1px solid #000; border-radius: 5px; background-color: #fff; cursor: pointer;">
                    <option value="Aktif">Aktif</option>
                    <option value="Non-Aktif">Non-Aktif</option>
                </select>
            </div>

            <div style="display: flex; justify-content: space-between; gap: 20px; margin-top: 20px;">
                <button type="button" onclick="closeModalTambah()" class="btn-cancel" style="flex: 1;"> 
                    Cancel
                </button>
                <button type="submit" class="btn-save" style="flex: 1;">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>



<div id="modalEditKaryawan" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
    <div style="background-color: #fff; padding: 30px; border-radius: 20px; width: 450px;">
        <h2 style="text-align: center; font-size: 24px; font-weight: 800; margin-bottom: 25px;">Ubah Data Karyawan</h2>

        <form id="formEditKaryawan" method="POST">
            @csrf
            @method('PUT') <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">Kode AO*</label>
                <input type="text" name="kode_ao" id="edit_kode_ao" required style="width: 100%; padding: 10px; border: 1px solid #000; border-radius: 5px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">Nama*</label>
                <input type="text" name="nama" id="edit_nama" required style="width: 100%; padding: 10px; border: 1px solid #000; border-radius: 5px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">Username*</label>
                <input type="text" name="username" id="edit_username" required style="width: 100%; padding: 10px; border: 1px solid #000; border-radius: 5px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">Password</label>
                <input type="password" name="password" id="edit_password" placeholder="Kosongkan jika tidak diubah" style="width: 100%; padding: 10px; border: 1px solid #000; border-radius: 5px;">
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">Status*</label>
                <select name="status" id="edit_status" required style="width: 100%; padding: 10px; border: 1px solid #000; border-radius: 5px; background-color: #fff;">
                    <option value="Aktif">Aktif</option>
                    <option value="Non-Aktif">Non-Aktif</option>
                </select>
            </div>

            <div style="display: flex; justify-content: space-between; gap: 20px;">
                <button type="button" onclick="closeModalEdit()" class="btn-cancel" style="flex: 1; padding: 10px; border-radius: 5px; cursor: pointer;">Cancel</button>
                <button type="submit" class="btn-save" style="flex: 1; padding: 10px; border-radius: 5px; cursor: pointer; background: #3f36b1; color: #fff; border: none;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div id="modalDetailKaryawan" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
    <div style="background-color: #fff; padding: 30px; border-radius: 20px; width: 450px; animation: zoomInModal 0.3s ease-out;">
        
        <h2 style="text-align: center; font-size: 24px; font-weight: 800; margin-bottom: 25px;">Detail</h2>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 700; margin-bottom: 5px;">Kode AO*</label>
            <input type="text" id="det_kode_ao" readonly style="width: 100%; padding: 10px; border: 1px solid #000; border-radius: 5px; background-color: #f9f9f9; cursor: not-allowed;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 700; margin-bottom: 5px;">Nama*</label>
            <input type="text" id="det_nama" readonly style="width: 100%; padding: 10px; border: 1px solid #000; border-radius: 5px; background-color: #f9f9f9; cursor: not-allowed;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 700; margin-bottom: 5px;">Username*</label>
            <input type="text" id="det_username" readonly style="width: 100%; padding: 10px; border: 1px solid #000; border-radius: 5px; background-color: #f9f9f9; cursor: not-allowed;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 700; margin-bottom: 5px;">Password*</label>
            <input type="text" value="********" readonly style="width: 100%; padding: 10px; border: 1px solid #000; border-radius: 5px; background-color: #f9f9f9; cursor: not-allowed;">
        </div>

        <div style="margin-bottom: 30px;">
            <label style="display: block; font-weight: 700; margin-bottom: 5px;">Status*</label>
            <input type="text" id="det_status" readonly style="width: 100%; padding: 10px; border: 1px solid #000; border-radius: 5px; background-color: #f9f9f9; cursor: not-allowed;">
        </div>

        <div style="display: flex; justify-content: center;">
            <button type="button" onclick="closeModalDetail()" class="btn-cancel" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; cursor: pointer;">Close</button>
        </div>
    </div>
</div>

<div id="modalExportNasabah" class="modal-overlay" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color: #fff; margin: 10% auto; padding: 25px; border-radius: 15px; width: 350px; font-family: 'Inter', sans-serif; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        
        <h2 style="text-align: center; font-size: 20px; font-weight: 800; margin-bottom: 25px;">Filter Data Nasabah</h2>
        
        <form action="{{ route('admin.nasabah.export') }}" method="GET">
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px; font-size: 14px;">Tanggal Awal</label>
                <div style="display: flex; gap: 5px;">
                    <input type="date" name="tanggal_awal" class="form-control-modal" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 5px;" required>
                </div>
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px; font-size: 14px;">Tanggal Akhir</label>
                <div style="display: flex; gap: 5px;">
                    <input type="date" name="tanggal_akhir" class="form-control-modal" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 5px;" required>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeModalExportNasabah()" style="background: #ff4d4d; color: white; border: none; padding: 8px 15px; border-radius: 20px; font-weight: 700; cursor: pointer; font-size: 12px;">Cancel</button>
                
                <button type="submit" class="btn-export-excel" style="background-color: #28a745; color: white; border: none; padding: 8px 20px; border-radius: 20px; font-weight: 700; display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 12px;">
                    <i class="fa-solid fa-file-excel"></i> Export
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modalFilterNasabah" class="modal-overlay" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color: #fff; margin: 10% auto; padding: 25px; border-radius: 15px; width: 350px; font-family: sans-serif; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <h2 style="text-align: center; font-size: 20px; font-weight: 800; margin-bottom: 25px;">Filter Data</h2>
        
        <form id="formFilterNasabah">
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">Tanggal Awal</label>
                <input type="date" id="tgl_awal_filter" name="tanggal_awal" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px;" required>
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">Tanggal Akhir</label>
                <input type="date" id="tgl_akhir_filter" name="tanggal_akhir" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px;" required>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeModalFilter()" style="background: #ff4d4d; color: white; border: none; padding: 8px 15px; border-radius: 20px; cursor: pointer;">Cancel</button>
                <button type="button" onclick="applyFilterAJAX(event)" class="btn-action-green" style="background-color: #28a745; color: white; border: none; padding: 8px 25px; border-radius: 20px; font-weight: 700; cursor: pointer;">
                    <i class="fa-solid fa-sliders"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modalExportPelaporan" class="modal-overlay" style="display: none;">
    <div class="modal-content-karyawan">
        <h2 style="text-align: center;">Export Pelaporan</h2>
        
        <form action="{{ route('admin.pelaporan.export') }}" method="GET">
            <div class="form-group-karyawan">
                <label>Tanggal Awal</label>
                <input type="date" name="tanggal_awal" required style="width: 100%; padding: 8px;">
            </div>

            <div class="form-group-karyawan" style="margin-top: 20px;">
                <label>Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" required style="width: 100%; padding: 8px;">
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 30px; gap: 10px;">
                <button type="button" onclick="closeModalExportPelaporan()" style="background: #ff4d4d; color: white; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer;">Cancel</button>
                
                <button type="submit" id="btnExportMurni" class="btn-tambah" style="background-color: #44c759; color: white; border: none; padding: 10px 30px; border-radius: 30px; cursor: pointer;">
                    <i class="fa-solid fa-file-excel"></i> Export
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modalTambahKunjungan" class="modal-custom" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
    <div style="background-color: white; padding: 25px; border-radius: 15px; width: 500px; max-width: 90%; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
            <h3 style="margin: 0; font-weight: 800;">Tambah Jadwal Kunjungan</h3>
            <button onclick="closeModalKunjungan()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        
       <form id="formTambahKunjungan">
            @csrf
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">Nama AO (Karyawan)</label>
                <select name="karyawan_id" id="selectKaryawan" required style="width: 100%; padding: 10px; border: 2px solid #000; border-radius: 8px;">
                    <option value="">-- Pilih AO --</option>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">Nama Nasabah</label>
                <input type="text" name="nama_nasabah" required style="width: 100%; padding: 10px; border: 2px solid #000; border-radius: 8px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">Alamat</label>
                <input type="text" name="alamat_nasabah" required style="width: 100%; padding: 10px; border: 2px solid #000; border-radius: 8px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">KOL</label>
                <select name="kol" required style="width: 100%; padding: 10px; border: 2px solid #000; border-radius: 8px;">
                    <option value="1">KOL 1</option>
                    <option value="2">KOL 2</option>
                    <option value="3">KOL 3</option>
                    <option value="4">KOL 4</option>
                    <option value="5">KOL 5</option>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">Bulan</label>
                <input type="month" name="bulan" required style="width: 100%; padding: 10px; border: 2px solid #000; border-radius: 8px;">
            </div>

           <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">Tanggal Kunjungan</label>
                <input type="date" name="tanggal" required style="width: 100%; padding: 10px; border: 2px solid #000; border-radius: 8px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">No. Angsuran</label>
                <input type="text" name="no_angsuran" required style="width: 100%; padding: 10px; border: 2px solid #000; border-radius: 8px;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeModalKunjungan()" style="padding: 10px 20px; border-radius: 8px; border: 2px solid #000; background: #fff; font-weight: 700; cursor: pointer;">Batal</button>
                <button type="submit" style="padding: 10px 20px; border-radius: 8px; background: #28a745; color: #fff; border: 2px solid #000; font-weight: 700; cursor: pointer;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="modalTambahNasabah" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
    <div style="background-color: #fff; padding: 30px; border-radius: 20px; width: 450px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); position: relative;">
        
        <h2 style="text-align: center; font-size: 24px; font-weight: 800; margin-bottom: 25px;">Tambah Data Nasabah</h2>

       <form action="{{ route('nasabah.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">No. Anggota*</label>
                <input type="text" name="no_angsuran" required placeholder="Masukkan nomor angsuran" style="width: 100%; padding: 10px; border: 1px solid #000; border-radius: 8px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">Nama Nasabah*</label>
                <input type="text" name="nasabah" required placeholder="Nama lengkap nasabah" style="width: 100%; padding: 10px; border: 1px solid #000; border-radius: 8px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">Alamat*</label>
                <textarea name="alamat" required placeholder="Alamat lengkap" style="width: 100%; padding: 10px; border: 1px solid #000; border-radius: 8px; height: 80px;"></textarea>
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">KOL*</label>
                <select name="kol" required style="width: 100%; padding: 10px; border: 1px solid #000; border-radius: 8px; background: white;">
                    <option value="">-- Pilih KOL --</option>
                    <option value="1">KOL 1</option>
                    <option value="2">KOL 2</option>
                    <option value="3">KOL 3</option>
                    <option value="4">KOL 4</option>
                    <option value="5">KOL 5</option>
                </select>
            </div>

            <div style="display: flex; justify-content: space-between; gap: 20px;">
                <button type="button" onclick="closeModalTambahNasabah()" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid #ccc; background: #eee;">Batal</button>
                <button type="submit" style="flex: 1; padding: 12px; border-radius: 10px; background: #28a745; color: white; border: none; cursor: pointer; font-weight: 700;">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

