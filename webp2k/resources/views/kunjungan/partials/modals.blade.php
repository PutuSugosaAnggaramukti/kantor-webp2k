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

                <div class="form-group mb-3">
                    <label>Bukti Transfer (Jika Nasabah Bayar Transfer)</label>
                    <input type="file" name="bukti_transfer" class="form-control" accept="image/*">
                    <small class="text-muted">*Isi jika nasabah melakukan pembayaran via transfer di tempat.</small>
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

<div id="modalManual" class="modal-overlay" style="display: none; align-items: center; justify-content: center;">
    <div class="modal-content" style="max-width: 500px; width: 90%; max-height: 90vh; border-radius: 20px; padding: 0; overflow: hidden; display: flex; flex-direction: column;">
        <div class="modal-header" style="padding: 20px 30px 10px; border-bottom: 1px solid #eee;">
            <h2 style="font-weight: 700; color: #000; margin: 0; text-align: center;">Kunjungan Mandiri</h2>
        </div>
        
        <form id="formKunjunganMandiri" action="{{ route('kunjungan.store') }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; overflow: hidden;">
            @csrf
           <div class="modal-body" style="padding: 10px 30px; overflow-y: auto; flex: 1;">
            
            <div class="form-group mb-3">
                <label>No. Angsuran</label>
                <input type="text" name="no_nasabah" class="form-control" placeholder="Contoh: 123456" required>
            </div>

            <div class="form-group mb-3">
                <label>Nama Nasabah</label>
                <input type="text" name="nama_nasabah" class="form-control" placeholder="Nama Lengkap Nasabah" required>
            </div>

            <div class="form-group mb-3">
                <label>Alamat Nasabah</label>
                <textarea name="alamat_nasabah" class="form-control" placeholder="Alamat Lengkap (Dusun, RT/RW, Desa)" rows="3" required></textarea>
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
                <textarea name="catatan" class="form-control" rows="3" required></textarea>
            </div>

            <div class="form-group mb-3">
                <label>Nominal Kesanggupan Bayar (Rp)</label>
                <input type="number" name="nominal_janji_bayar" class="form-control" placeholder="Contoh: 500000">
            </div>

            <div class="form-group mb-3">
                <label>Tanggal Kesanggupan Bayar</label>
                <input type="date" name="tgl_janji_bayar" class="form-control">
            </div>

            <div class="form-group mb-3">
                <label style="font-weight: 600;">Foto Kunjungan (Wajib GPS)</label>
                <input type="file" name="foto_kunjungan[]" class="form-control" multiple accept=".jpg,.jpeg" required>
            </div>

            <div class="form-group mb-3">
                <label>Bukti Transfer (Jika Nasabah Bayar Langsung)</label>
                <input type="file" name="bukti_transfer" class="form-control" accept="image/*" style="border: 1px solid #ddd; padding: 8px;">
                <small class="text-muted" style="font-size: 10px;">
                    <i class="fas fa-info-circle"></i> Gunakan ini jika nasabah membayar via transfer/tunai di tempat. (Format: JPG/PNG)
                </small>
            </div>

            <input type="hidden" name="koordinat" id="manual-koordinat">
            <p id="manual-location-status" style="font-size: 11px; color: #888;">
                <i class="fas fa-spinner fa-spin"></i> Mendeteksi lokasi GPS...
            </p>
        </div>

            <div class="modal-footer" style="padding: 15px 30px 20px; border-top: 1px solid #eee; background: #fff; text-align: right;">
                <button type="button" class="btn-cancel" onclick="closeModal()" style="margin-right: 10px;">Cancel</button>
                <button type="submit" class="btn-save">Save Kunjungan</button>
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


<div id="modalTambahJadwalAO" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
    <div style="background:#fff; width:450px; margin:50px auto; padding:25px; border-radius:12px; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
        <h3 style="margin-top:0; color:#333;">Tambah Jadwal Kunjungan</h3>
        <hr style="border:0; border-top:1px solid #eee; margin:15px 0;">
        
        <form id="formTambahJadwalAO">
            <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label style="display:block; font-weight:bold; margin-bottom:5px; font-size:13px;">Kode AO</label>
                    <input type="text" class="form-control" value="{{ Auth::guard('karyawan')->user()->kode_ao }}" readonly style="background:#eee; cursor:not-allowed; width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                </div>
                <div style="flex: 2;">
                    <label style="display:block; font-weight:bold; margin-bottom:5px; font-size:13px;">Nama AO</label>
                    <input type="text" class="form-control" value="{{ Auth::guard('karyawan')->user()->nama }}" readonly style="background:#eee; cursor:not-allowed; width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                </div>
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Cari Nasabah</label>
                <select id="select_nasabah" class="form-control" style="width:100%; padding:8px;">
                    <option value="">-- Pilih Nasabah --</option>
                    @foreach($daftar_nasabah as $n)
                        <option value="{{ $n->no_angsuran }}" 
                                data-nama="{{ $n->nasabah }}" 
                                data-kode="{{ $n->kode }}" 
                                data-rekening="{{ $n->rekening_kredit }}"
                                data-alamat="{{ $n->alamat }}" 
                                data-kol="{{ $n->kol }}">
                            {{ $n->no_angsuran }} - {{ $n->nasabah }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="background:#f8f9fa; padding:12px; border-radius:8px; margin-bottom:15px; font-size:13px; border-left:4px solid #007bff;">
                <p style="margin:0 0 5px 0;"><strong>Kode:</strong> <span id="txt_kode">-</span></p>
                <p style="margin:0 0 5px 0;"><strong>Rekening Kredit:</strong> <span id="txt_rekening">-</span></p>
                <p style="margin:0;"><strong>Alamat:</strong> <span id="txt_alamat">-</span></p>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Tanggal Kunjungan</label>
                <input type="date" id="tanggal_kunjungan" class="form-control" style="width:100%; padding:8px;" value="{{ date('Y-m-d') }}">
            </div>

            <input type="hidden" id="no_angsuran">
            <input type="hidden" id="nama_nasabah">
            <input type="hidden" id="alamat_nasabah">
            <input type="hidden" id="kol_nasabah">
            <input type="hidden" id="bulan_input" value="{{ date('Y-m') }}">

            <div style="display:flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeModalAO()" style="background:#6c757d; color:#fff; border:none; padding:10px 20px; border-radius:6px; cursor:pointer;">Batal</button>
                <button type="button" onclick="simpanJadwalMandiri()" style="background:#28a745; color:#fff; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-weight:bold;">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEditJadwalGlobal" class="modal-custom-global">
    <div class="modal-custom-dialog">
        <div class="modal-custom-header">
            <h5>Ubah Jadwal Kunjungan</h5>
            <button type="button" class="close-btn" onclick="closeModalUbahJadwal()">&times;</button>
        </div>
        <form action="{{ route('kunjungan.updateJadwalGlobal') }}" method="POST">
            @csrf
            <div class="modal-custom-body">
                <label>Pilih Nasabah</label>
                <select name="id" class="form-control" required>
                    <option value="">-- Pilih jadwal nasabah yang ingin diubah --</option>
                    @foreach($data as $item)
                        @if(!$item->is_filled)
                            <option value="{{ $item->id }}">{{ $item->nama_nasabah }} ({{ date('d-m-Y', strtotime($item->tanggal)) }})</option>
                        @endif
                    @endforeach
                </select>

                <label>Tanggal Baru</label>
                <input type="date" name="tanggal" class="form-control" required>
            </div>
            <div class="modal-custom-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModalUbahJadwal()" style="border-radius: 20px; padding: 8px 20px;">Batal</button>
                <button type="submit" class="btn btn-warning" style="border-radius: 20px; font-weight: bold; padding: 8px 20px; background-color: #ffc107; border: none;">Update Jadwal</button>
            </div>
        </form>
    </div>
</div>