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
                    <option value="Non Aktif">Non-Aktif</option>
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
    <div style="background-color: white; padding: 25px; border-radius: 15px; width: 500px; max-width: 90%; box-shadow: 0 5px 15px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
            <h3 style="margin: 0; font-weight: 800;">Tambah Jadwal Kunjungan</h3>
            <button onclick="closeModalKunjungan()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        
        <form id="formTambahKunjungan" style="padding-bottom: 15px;"> @csrf
            <div style="margin-bottom: 12px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">Nama AO (Karyawan)</label>
                <select name="karyawan_id" id="selectKaryawan" required style="width: 100%; padding: 10px; border: 2px solid #000; border-radius: 8px;">
                    <option value="">-- Pilih AO --</option>
                    @php
                        $list_karyawan = isset($karyawans) ? $karyawans : \App\Models\Karyawan::where('status', 'aktif')
                                            ->where('kode_ao', 'LIKE', 'C-%')
                                            ->orderBy('nama', 'asc')
                                            ->get();
                    @endphp
                    @foreach($list_karyawan as $k)
                        <option value="{{ $k->id }}">{{ $k->nama }} ({{ $k->kode_ao }})</option>
                    @endforeach
                </select>
            </div>

           <div style="margin-bottom: 12px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">No. Anggota / Nasabah</label>
                <select name="no_angsuran" id="dropdown_no_angsuran" required class="select2-nasabah" style="width: 100%;">
                    <option value="">-- Cari No. Anggota atau Nama --</option>
                </select>
            </div>
            <div style="margin-bottom: 12px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">Nama Nasabah</label>
                <input type="text" name="nama_nasabah" id="display_nama" readonly style="width: 100%; padding: 10px; border: 2px solid #ccc; border-radius: 8px; background-color: #e9ecef;">
            </div>

            <div style="margin-bottom: 12px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">Alamat</label>
                <input type="text" name="alamat_nasabah" id="display_alamat" readonly style="width: 100%; padding: 10px; border: 2px solid #ccc; border-radius: 8px; background-color: #e9ecef;">
            </div>

            <div style="margin-bottom: 12px;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px;">KOL</label>
                <input type="text" name="kol" id="display_kol" readonly style="width: 100%; padding: 10px; border: 2px solid #ccc; border-radius: 8px; background-color: #e9ecef;">
            </div>

            <div style="display: flex; gap: 10px; margin-bottom: 20px;"> 
                <div style="flex: 1;">
                    <label style="display: block; font-weight: 700; margin-bottom: 5px;">Bulan</label>
                    <input type="month" name="bulan" required style="width: 100%; padding: 10px; border: 2px solid #000; border-radius: 8px;" value="{{ date('Y-m') }}">
                </div>
                <div style="flex: 1;">
                    <label style="display: block; font-weight: 700; margin-bottom: 5px;">Tanggal Kunjungan</label>
                    <input type="date" name="tanggal" required style="width: 100%; padding: 10px; border: 2px solid #000; border-radius: 8px;" value="{{ date('Y-m-d') }}">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 25px; padding-bottom: 10px;"> 
                <button type="button" onclick="closeModalKunjungan()" style="padding: 10px 20px; border-radius: 8px; border: 2px solid #000; background: #fff; font-weight: 700; cursor: pointer;">Batal</button>
                
                <button type="button" onclick="simpanJadwalManual()" style="padding: 10px 20px; border-radius: 8px; background: #28a745; color: #fff; border: 2px solid #000; font-weight: 700; cursor: pointer;">
                    Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modalTambahNasabah" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; overflow-y: auto;">
    <div style="background-color: #fff; padding: 30px; border-radius: 20px; width: 95%; max-width: 1100px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); margin: 20px auto;">
        
        <h2 style="text-align: center; font-size: 24px; font-weight: 800; margin-bottom: 25px;">Tambah Data Nasabah</h2>

        <form action="{{ route('nasabah.store') }}" method="POST">
            @csrf
            
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                
                <div style="flex: 1; min-width: 300px;">
                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-weight: 700; margin-bottom: 3px;">Kode</label>
                        <input type="text" name="kode" placeholder="Contoh: PG.001" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-weight: 700; margin-bottom: 3px;">No. Angsuran*</label>
                        <input type="text" name="no_angsuran" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-weight: 700; margin-bottom: 3px;">Rekening Kredit</label>
                        <input type="text" name="rekening_kredit" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-weight: 700; margin-bottom: 3px;">Kode Nasabah</label>
                        <input type="text" name="kode_nasabah" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-weight: 700; margin-bottom: 3px;">Nama Nasabah*</label>
                        <input type="text" name="nasabah" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-weight: 700; margin-bottom: 3px;">Alamat*</label>
                        <textarea name="alamat" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px; height: 50px;"></textarea>
                    </div>
                </div>

                <div style="flex: 1; min-width: 300px;">
                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-weight: 700; margin-bottom: 3px;">Lama (Tenor)</label>
                        <input type="number" name="lama" placeholder="Bulan" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
                    </div>
                    <div style="display: flex; gap: 5px; margin-bottom: 10px;">
                        <div style="flex: 1;">
                            <label style="display: block; font-weight: 700; margin-bottom: 3px;">Tgl Pinjam</label>
                            <input type="date" name="tgl_pinjam" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; font-weight: 700; margin-bottom: 3px;">Tgl JT</label>
                            <input type="date" name="tgl_jt" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
                        </div>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-weight: 700; margin-bottom: 3px;">Nominal (Plafond)</label>
                        <input type="number" name="nominal" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-weight: 700; margin-bottom: 3px; color: red;">Sisa Pokok (OS)</label>
                        <input type="number" name="sisa_pokok" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
                    </div>
                    <div style="display: flex; gap: 5px; margin-bottom: 10px;">
                        <div style="flex: 1;">
                            <label style="display: block; font-weight: 700; margin-bottom: 3px;">Pokok/Bln</label>
                            <input type="number" name="pokok_per_bulan" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; font-weight: 700; margin-bottom: 3px;">Bunga/Bln</label>
                            <input type="number" name="bunga_per_bulan" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
                        </div>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-weight: 700; margin-bottom: 3px;">Kode AO</label>
                        <input type="text" name="kode_ao" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
                    </div>
                </div>

                <div style="flex: 1; min-width: 300px;">
                    <div style="display: flex; gap: 5px; margin-bottom: 10px;">
                        <div style="flex: 2;">
                            <label style="display: block; font-weight: 700; margin-bottom: 3px;">Tgk. Pokok</label>
                            <input type="number" name="tunggakan_pokok" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; font-weight: 700; margin-bottom: 3px;">Hari</label>
                            <input type="number" name="hari_pokok" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
                        </div>
                    </div>
                    <div style="display: flex; gap: 5px; margin-bottom: 10px;">
                        <div style="flex: 2;">
                            <label style="display: block; font-weight: 700; margin-bottom: 3px;">Tgk. Bunga</label>
                            <input type="number" name="tunggakan_bunga" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; font-weight: 700; margin-bottom: 3px;">Hari</label>
                            <input type="number" name="hari_bunga" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
                        </div>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-weight: 700; margin-bottom: 3px;">Denda</label>
                        <input type="number" name="denda" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 700; margin-bottom: 5px; color: #007bff;">Klasifikasi Nasabah (Kualitas)*</label>
                        <select name="kol" required style="width: 100%; padding: 10px; border: 2px solid #007bff; border-radius: 8px; background: white; font-weight: 700;">
                            <option value="">-- Pilihan Klasifikasi Nasabah --</option>
                            <option value="1">LANCAR</option>
                            <option value="2">DALAM PERHATIAN KHUSUS</option>
                            <option value="3">KURANG LANCAR</option>
                            <option value="4">DIRAGUKAN</option>
                            <option value="5">MACET</option>
                        </select>
                    </div>
                    <div style="display: flex; justify-content: space-between; gap: 10px; margin-top: 25px;">
                        <button type="button" onclick="closeModalTambahNasabah()" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid #ccc; background: #eee; font-weight: 700; cursor: pointer;">Batal</button>
                        <button type="submit" style="flex: 2; padding: 12px; border-radius: 10px; background: #28a745; color: white; border: none; cursor: pointer; font-weight: 700;">Simpan Data</button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<div id="importNasabahModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background-color: rgba(0,0,0,0.7); backdrop-filter: blur(2px); align-items: center; justify-content: center;">
    <div style="background-color: #fff; padding: 30px; border-radius: 25px; width: 450px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); position: relative; border: 2px solid #3b82f6;">
        
        <div style="text-align: center; margin-bottom: 25px;">
            <div style="background: #eff6ff; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                <i class="fa-solid fa-file-import" style="font-size: 28px; color: #3b82f6;"></i>
            </div>
            <h2 style="font-size: 22px; font-weight: 800; margin: 0; color: #1e293b;">Import Data Nasabah</h2>
            <p style="font-size: 13px; color: #64748b; margin-top: 5px;">Unggah file excel untuk memperbarui data reguler</p>
        </div>

        <form action="{{ route('admin.nasabah.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; margin-bottom: 10px; font-size: 14px; color: #334155;">Pilih File (Excel/CSV)</label>
                
                <div style="border: 2px dashed #3b82f6; border-radius: 15px; padding: 25px; text-align: center; background: #f8faff; position: relative; transition: all 0.3s;">
                    <input type="file" id="fileInputReguler" name="file" accept=".xlsx, .xls, .csv" required 
                           style="opacity: 0; position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer;"
                           onchange="updateFileName(this, 'fileNameTextReguler')">
                    
                    <i class="fa-solid fa-cloud-arrow-up" style="font-size: 32px; color: #3b82f6; margin-bottom: 12px; display: block;"></i>
                    <span id="fileNameTextReguler" style="font-size: 13px; font-weight: 700; color: #3b82f6;">Klik atau tarik file ke sini</span>
                </div>
                
                <div style="margin-top: 15px; background: #f1f5f9; padding: 15px; border-radius: 12px; border-left: 4px solid #3b82f6;">
                    <small style="display: block; font-weight: 800; color: #475569; margin-bottom: 5px; font-size: 11px; text-transform: uppercase;">Aturan Kolom Excel:</small>
                    <small style="display: block; color: #64748b; font-size: 12px; line-height: 1.6;">
                        • Kolom A: No_Angsuran<br>
                        • Kolom B: Nama Nasabah<br>
                        • Kolom C: Alamat<br>
                        • Kolom D: KOL (1-4)
                    </small>
                </div>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 25px;">
                <button type="button" onclick="closeModalImportNasabah()" style="flex: 1; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0; background: #f8fafc; color: #64748b; font-weight: 700; cursor: pointer; transition: 0.2s;">
                    Batal
                </button>
                <button type="submit" style="flex: 1; padding: 12px; border-radius: 12px; background: #3b82f6; color: white; border: none; font-weight: 700; cursor: pointer; box-shadow: 0 4px 0 #2563eb; transition: 0.2s;">
                    Mulai Import
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modalImport" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
    <div style="background-color: white; padding: 30px; border-radius: 15px; width: 450px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); position: relative; border: 3px solid #000;">
        <h3 style="margin-top: 0; color: #000; font-weight: 800; border-bottom: 2px solid #eee; padding-bottom: 15px; text-transform: uppercase;">
            <i class="fa-solid fa-file-excel"></i> Import Jadwal AO
        </h3>
        
        <form action="{{ route('admin.datakunjungan.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            {{-- Bagian Pilih AO --}}
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 800; margin-bottom: 8px; color: #333;">Pilih Account Officer (AO)</label>
                <select name="karyawan_id" required style="width: 100%; padding: 12px; border: 2px solid #000; border-radius: 8px; font-weight: 700; background: #fff;">
                    <option value="">-- Pilih AO Penerima Jadwal --</option>
                    @foreach($karyawans ?? \App\Models\Karyawan::where('status', 'aktif')->get() as $ao)
                        <option value="{{ $ao->id }}">{{ $ao->nama }} ({{ $ao->kode_ao }})</option>
                    @endforeach
                </select>
            </div>

            {{-- INPUT TANGGAL KUNJUNGAN BARU --}}
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 800; margin-bottom: 8px; color: #333;">Rencana Tanggal Kunjungan</label>
                <input type="date" name="tanggal_kunjungan" required 
                       style="width: 100%; padding: 12px; border: 2px solid #000; border-radius: 8px; font-weight: 700; font-family: inherit;">
                <small style="color: #666; font-style: italic;">*Seluruh nasabah di Excel akan dijadwalkan pada tanggal ini.</small>
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; font-weight: 800; margin-bottom: 8px; color: #333;">File Excel (.xlsx)</label>
                <input type="file" name="file_excel" accept=".xlsx" required style="width: 100%; padding: 10px; border: 2px dashed #ccc; border-radius: 8px;">
                <div style="margin-top: 10px; background: #fff5f5; padding: 10px; border-radius: 6px; border: 1px solid #feb2b2;">
                    <small style="display: block; color: #c53030; font-weight: 700; font-size: 11px;">
                        <i class="fa-solid fa-info-circle"></i> Format Kolom:
                    </small>
                    <small style="display: block; color: #4a5568; font-size: 11px; font-weight: 600;">
                        A: Nama Nasabah | B: KOL | C: Bulan (YYYY-MM)
                    </small>
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeModalImport()" style="padding: 10px 20px; border-radius: 8px; border: 2px solid #000; background: #f8f9fa; font-weight: 700; cursor: pointer;">Batal</button>
                <button type="submit" style="padding: 10px 20px; border-radius: 8px; border: none; background: #4e4bc1; color: white; font-weight: 700; cursor: pointer; box-shadow: 0 4px 0 #2d2a8a;">
                    Proses Import
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modalDetailKunjungan" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; width: 100%; max-width: 500px; border-radius: 15px; overflow: hidden; display: flex; flex-direction: column; max-height: 90vh;">
        <div style="padding: 15px 20px; background: #f8f9fa; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-weight: bold;">Detail Hasil Kunjungan</h3>
            <button onclick="closeVisitDetail()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        
        <div style="padding: 20px; overflow-y: auto;">
            <div style="margin-bottom: 15px;">
                <label style="font-weight: bold; color: #666; font-size: 12px; display: block; margin-bottom: 5px;">FOTO KUNJUNGAN</label>
                <img id="view-foto" src="" style="width: 100%; border-radius: 10px; border: 1px solid #ddd; height: 250px; object-fit: cover;">
                <small id="view-jam" style="display: block; margin-top: 5px; color: #777; font-style: italic;"></small>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="font-weight: bold; color: #666; font-size: 12px; display: block;">KOORDINAT</label>
                    <a id="view-koordinat-link" href="#" target="_blank" style="text-decoration: none;">
                        <span id="view-koordinat" style="font-weight: bold; color: #3498db; font-size: 13px;"></span>
                    </a>
                </div>
                <div>
                    <label style="font-weight: bold; color: #666; font-size: 12px; display: block;">JANJI BAYAR</label>
                    <span id="view-janji" style="font-weight: bold; color: #e67e22;"></span>
                </div>
                <div style="grid-column: span 2;">
                    <label style="font-weight: bold; color: #666; font-size: 12px; display: block;">NOMINAL KESANGGUPAN</label>
                    <span id="view-nominal" style="font-weight: bold; color: #27ae60; font-size: 16px;"></span>
                </div>
            </div>

            <div style="margin-bottom: 5px;">
                <label style="font-weight: bold; color: #666; font-size: 12px; display: block; margin-bottom: 5px;">CATATAN KUNJUNGAN</label>
                <div id="view-catatan" style="background: #fdf9f4; padding: 10px; border-radius: 8px; border-left: 4px solid #f39c12; font-style: italic; font-size: 13px;"></div>
            </div>
        </div>

        <div style="padding: 15px 20px; background: #f8f9fa; border-top: 1px solid #ddd; text-align: right;">
            <button onclick="closeVisitDetail()" style="padding: 8px 20px; border-radius: 5px; border: 1px solid #ccc; cursor: pointer;">Tutup</button>
        </div>
    </div>
</div>

<div id="modalImportHB" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 99999; background-color: rgba(0,0,0,0.7); backdrop-filter: blur(2px); justify-content: center; align-items: center;">
    <div style="background-color: #fff; padding: 30px; border-radius: 20px; width: 450px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); position: relative; border: 3px solid #d9534f; margin: auto;">
        
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="background: #fff5f5; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                <i class="fa-solid fa-file-circle-exclamation" style="font-size: 28px; color: #d9534f;"></i>
            </div>
            <h2 style="font-size: 22px; font-weight: 800; margin: 0; color: #000;">Import Nasabah HB</h2>
            <p style="font-size: 13px; color: #666; margin-top: 5px;">Unggah data khusus nasabah Hapus Buku</p>
        </div>

        <form action="{{ route('admin.nasabah.import_hb') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; margin-bottom: 8px; font-size: 14px;">Pilih File Excel (.xlsx)</label>
                
                <div style="border: 2px dashed #d9534f; border-radius: 12px; padding: 20px; text-align: center; background: #fffcfc; position: relative;">
                    <input type="file" name="file_excel" id="fileInputHB" accept=".xlsx, .xls, .csv" required 
                        style="opacity: 0; position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer;"
                        onchange="updateFileName(this, 'fileNameTextHB')">
                    
                    <i class="fa-solid fa-cloud-arrow-up" style="font-size: 30px; color: #d9534f; margin-bottom: 10px; display: block;"></i>
                    <span id="fileNameTextHB" style="font-size: 12px; font-weight: 700; color: #d9534f;">Klik atau tarik file ke sini</span>
                </div>
                
                <div style="margin-top: 15px; background: #f8f9fa; padding: 12px; border-radius: 10px; border-left: 4px solid #d9534f;">
                    <small style="display: block; font-weight: 800; color: #333; margin-bottom: 4px;">Aturan Kolom Excel:</small>
                    <small style="display: block; color: #555; font-size: 11px; line-height: 1.5;">
                        • Kolom A: No. Angsuran<br>
                        • Kolom B: Nama Nasabah<br>
                        • Kolom C: Alamat<br>
                        • Kolom D: Nominal (Akan tersimpan sebagai status HB)
                    </small>
                </div>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 25px;">
                <button type="button" onclick="closeModalImportHB()" style="flex: 1; padding: 12px; border-radius: 12px; border: 1px solid #ccc; background: #eee; font-weight: 700; cursor: pointer;">
                    Batal
                </button>
                <button type="submit" style="flex: 1; padding: 12px; border-radius: 12px; background: #d9534f; color: white; border: none; font-weight: 700; cursor: pointer; box-shadow: 0 4px 0 #a93c39;">
                    Proses Import
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modalPilihHapus" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
    <div style="background:#fff; width:500px; margin:50px auto; padding:20px; border-radius:10px;">
        <h3>Pilih Jadwal yang Akan Dibatalkan</h3>
        <hr>
        <div id="listJadwalHapus" style="max-height: 400px; overflow-y: auto; margin-bottom: 20px;">
            </div>
        <div style="display:flex; justify-content: flex-end; gap: 10px;">
            <button onclick="closeModalPilihHapus()" style="background:#6c757d; color:#fff; border:none; padding:8px 15px; border-radius:5px;">Batal</button>
            <button onclick="prosesHapusPilihan()" style="background:#dc3545; color:#fff; border:none; padding:8px 15px; border-radius:5px;">Hapus yang Dipilih</button>
        </div>
    </div>
</div>