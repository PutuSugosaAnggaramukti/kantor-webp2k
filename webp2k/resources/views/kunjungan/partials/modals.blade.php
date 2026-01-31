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
                    <input type="text" id="display-no" class="form-control" disabled>
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
                    <label>Tanggal Kesanggupan Bayar (Opsional)</label>
                    <input type="date" name="tgl_janji_bayar" class="form-control">
                    <small style="color: #f39c12;"><i class="fas fa-info-circle"></i> Isi jika nasabah menjanjikan pembayaran.</small>
                </div>

                <div class="form-group mb-3">
                    <label>Foto Kunjungan (Maks 5MB)</label>
                    <input type="file" name="foto_kunjungan" class="form-control" required>
                </div>

                <input type="hidden" name="koordinat" id="form-koordinat">
                <p id="location-status" style="font-size: 11px; color: #888;">
                    <i class="fas fa-spinner fa-spin"></i> Mendeteksi lokasi GPS...
                </p>
            </div>

            <div class="modal-footer" style="padding: 15px 30px 20px; border-top: 1px solid #eee; background: #fff; text-align: right;">
                <button type="button" class="btn-cancel" onclick="closeModal()" style="margin-right: 10px;">Cancel</button>
                <button type="submit" class="btn-save">Save</button>
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

