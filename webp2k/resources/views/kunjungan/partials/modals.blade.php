<div id="visitModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px; border-radius: 20px; padding: 30px;">
        <div class="modal-header" style="text-align: center; border: none; margin-bottom: 20px;">
            <h2 style="font-weight: 700; color: #000;">Form Kunjungan</h2>
        </div>
        
       <form action="{{ route('kunjungan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="no_nasabah" id="form-no-nasabah">
            <input type="hidden" name="nama_nasabah" id="form-nama-nasabah">

            <div class="form-group">
                <label>No Nasabah</label>
                <input type="text" id="display-no" class="form-control" disabled>
            </div>

            <div class="form-group">
                <label>Nama Nasabah</label>
                <input type="text" id="display-nama" class="form-control" disabled>
            </div>

            <div class="form-group">
                <label>Keterangan Nasabah</label>
                <input type="text" name="keterangan_nasabah" class="form-control">
            </div>

            <div class="form-group">
                <label>Apakah nasabah ada di lokasi?</label>
                <select name="ada_di_lokasi" class="form-control">
                    <option value="Ada">Ada</option>
                    <option value="Tidak Ada">Tidak Ada</option>
                </select>
            </div>

            <div class="form-group">
                <label>Catatan</label>
                <textarea name="catatan" class="form-control"></textarea>
            </div>

            <div class="form-group">
                <label>Foto Kunjungan (Maks 5MB)</label>
                <input type="file" name="foto_kunjungan" class="form-control" required>
            </div>

            <input type="hidden" name="koordinat" id="form-koordinat" value="-7.888581, 110.3239571">

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
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