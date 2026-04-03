<div id="visitModal" class="modal-overlay" style="display: none; align-items: center; justify-content: center;">
    <div class="modal-content" style="
        max-width: 500px; 
        width: 90%; 
        max-height: 90vh; /* Membatasi tinggi modal 90% dari layar */
        border-radius: 20px; 
        padding: 0; /* Padding dipindah ke dalam agar scroll rapi */
        overflow: hidden; /* Mencegah modal pecah */
        display: flex;
        flex-direction: column;
    ">
        <div class="modal-header" style="padding: 20px 30px 10px; border-bottom: 1px solid #eee;">
            <h2 style="font-weight: 700; color: #000; margin: 0; text-align: center;">Form Kunjungan</h2>
        </div>
        
        <form action="{{ route('kunjungan.store') }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; overflow: hidden;">
            @csrf
            <div class="modal-body" style="padding: 10px 30px; overflow-y: auto; flex: 1;">
                
                <input type="hidden" name="no_nasabah" id="form-no-nasabah">
                <input type="hidden" name="nama_nasabah" id="form-nama-nasabah">

                <div class="form-group mb-3">
                    <label>Kode AO</label>
                    <input type="text" id="display-kode-ao" class="form-control" disabled>
                </div>

                <div class="form-group mb-3">
                    <label>Nama Nasabah</label>
                    <input type="text" id="display-nama" class="form-control" disabled>
                </div>

                <div class="form-group mb-3">
                    <label>Apakah nasabah ada di lokasi?</label>
                    <select name="ada_di_lokasi" class="form-control">
                        <option value="Ada">Ada</option>
                        <option value="Tidak Ada">Tidak Ada</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label>Hasil Kunjungan</label>
                    <textarea name="catatan" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group mb-3">
                    <label>Nominal Kesanggupan Bayar (Rp)</label>
                    <input type="number" name="nominal_janji_bayar" class="form-control" placeholder="Contoh: 500000">
                    <small style="color: #f39c12;"><i class="fas fa-info-circle"></i> Isi angka saja tanpa titik/rupiah.</small>
                </div>

                <div class="form-group mb-3">
                    <label>Tanggal Kesanggupan Bayar (Opsional)</label>
                    <input type="date" name="tgl_janji_bayar" class="form-control">
                    <small style="color: #f39c12;"><i class="fas fa-info-circle"></i> Isi jika nasabah menjanjikan pembayaran.</small>
                </div>

                <div class="form-group mb-3">
                    <label style="font-weight: 600;">Foto Kunjungan (Wajib GPS Nyala)</label>
                    <input type="file" name="foto_kunjungan[]" class="form-control" multiple accept=".jpg,.jpeg" required 
                        style="border: 1px solid #ddd; padding: 8px;">
                    
                    <div style="background: #fff8e1; border-left: 4px solid #ffc107; padding: 10px; margin-top: 10px; border-radius: 4px;">
                        <p style="font-size: 11px; color: #856404; margin: 0; line-height: 1.4;">
                            <i class="fas fa-exclamation-triangle"></i> <strong>PENTING:</strong><br>
                            - Gunakan foto <strong>ASLI</strong> dari kamera HP (format .JPG).<br>
                            - Pastikan <strong>GPS Kamera</strong> aktif saat memotret.<br>
                            - Foto Screenshot atau dari WhatsApp akan <strong>DITOLAK</strong> karena tidak memiliki data GPS asli.
                        </p>
                    </div>
                </div>

                <input type="hidden" name="koordinat" id="form-koordinat">
                <p id="location-status" style="font-size: 11px; color: #888;">
                    <i class="fas fa-spinner fa-spin"></i> Mendeteksi lokasi GPS...
                </p>
            </div>

            <div class="modal-footer" style="padding: 15px 30px 20px; border-top: 1px solid #eee; background: #fff; text-align: right;">
                <button type="button" class="btn-cancel" onclick="closeModal()" style="margin-right: 10px;">Cancel</button>
                <button type="button" id="btn-save-kunjungan" class="btn-save">Save</button>
            </div>
        </form>
    </div>
</div>


<div id="detailModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <h2 style="text-align: center; margin-bottom: 20px;">Detail Nasabah</h2>
        
        <div class="info-card"><label>Kode:</label><p id="detail-kode">-</p></div>
        <div class="info-card"><label>No. Angsuran:</label><p id="detail-angsuran">-</p></div>
        <div class="info-card"><label>Nama:</label><p id="detail-nama">-</p></div>
        <div class="info-card"><label>Alamat:</label><p id="detail-alamat">-</p></div>
        <div class="info-card"><label>Nominal:</label><p id="detail-nominal">-</p></div>
        <div class="info-card"><label>Sisa Pokok:</label><p id="detail-sisa">-</p></div>
        <div class="info-card"><label>KOL:</label><p id="detail-kol">-</p></div>
        <div class="info-card"><label>Kode AO:</label><p id="detail-kode-ao">-</p></div>
        <div class="info-card"><label>AO:</label><p id="detail-nama-ao">-</p></div>

        <button onclick="closeDetailModal()" style="width: 100%; margin-top: 20px; padding: 10px; border-radius: 10px; cursor: pointer;">Tutup</button>
    </div>
</div>

<div id="modalManual" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); backdrop-filter: blur(3px); overflow-y: auto;">
    <div style="background:white; margin:20px auto; padding:25px; width:90%; max-width:450px; border-radius:15px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); font-family: 'Poppins', sans-serif; position: relative;">
        
        <h3 style="text-align:center; margin-bottom:20px; font-weight:bold;">Form Kunjungan</h3>
        
        <form action="{{ route('kunjungan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div style="max-height: 60vh; overflow-y: auto; padding-right: 5px; margin-bottom: 15px;">
                
                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Nama Nasabah</label>
                    <input type="text" name="nama_nasabah" style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;" required placeholder="Masukkan nama nasabah">
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Nomor Anggota</label>
                    <input type="text" name="no_nasabah" style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;" placeholder="Masukkan nomor">
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">KOL (Jika tidak tahu bisa dikosongi saja)</label>
                    <select name="kol" style="width:100%; padding:10px; border-radius:10px; border:1px solid #ddd; background:white;">
                        <option value="">-- Pilih KOL (Jika tahu) --</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                    <small style="color: #888; font-size: 11px;">Biarkan kosong jika tidak yakin.</small>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Apakah nasabah ada di lokasi?</label>
                    <select name="ada_di_lokasi" style="width:100%; padding:10px; border-radius:10px; border:1px solid #ddd; background:white;" required>
                        <option value="Ada">Ada</option>
                        <option value="Tidak Ada">Tidak Ada</option>
                    </select>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Hasil Kunjungan</label>
                    <textarea name="catatan" style="width:100%; padding:10px; border-radius:10px; border:1px solid #ddd;" rows="3" required placeholder="Tulis hasil kunjungan..."></textarea>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Tanggal Kesanggupan Bayar</label>
                    <input type="date" name="tgl_janji_bayar" style="width:100%; padding:10px; border-radius:10px; border:1px solid #ddd;">
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Foto Kunjungan</label>
                    <div style="border:1px solid #ddd; padding:10px; border-radius:10px;">
                        <input type="file" name="foto_kunjungan" accept="image/*" capture="camera" required style="width:100%;">
                    </div>
                    <div style="font-size: 12px; color: #28a745; margin-top: 8px; font-weight: bold;">
                        <i class="fa-solid fa-circle-check"></i> Lokasi Terkunci
                    </div>
                </div>

            </div> <input type="hidden" name="koordinat" id="manual_koordinat">

            <div style="display: flex; gap: 15px; background: white; padding-top: 10px;">
                <button type="button" onclick="closeManualModal()" style="flex: 1; background:#e91e63; color:white; padding:12px; border:none; border-radius:12px; font-weight:bold; cursor:pointer;">
                    Cancel
                </button>
                <button type="submit" style="flex: 1; background:#5c59d1; color:white; padding:12px; border:none; border-radius:12px; font-weight:bold; cursor:pointer;">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>