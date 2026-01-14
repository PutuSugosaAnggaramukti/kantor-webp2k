<div id="modalEditKaryawan" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 20px; width: 400px;">
        <h2 style="text-align: center; margin-bottom: 20px;">Ubah Data Karyawan</h2>
        
        <div style="margin-bottom: 15px;">
            <label>Kode AO*</label>
            <input type="text" id="edit-kode" style="width: 100%; padding: 10px; border: 1px solid #333; border-radius: 5px;" readonly>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label>Nama*</label>
            <input type="text" id="edit-nama" style="width: 100%; padding: 10px; border: 1px solid #333; border-radius: 5px;">
        </div>

        <div style="display: flex; justify-content: space-between; gap: 20px; margin-top: 20px;">
            <button onclick="closeEditModal()" style="background: #ef4444; color: white; border: none; padding: 10px 30px; border-radius: 10px; cursor: pointer;">Cancel</button>
            <button style="background: #4f46e5; color: white; border: none; padding: 10px 30px; border-radius: 10px; cursor: pointer;">Save</button>
        </div>
    </div>
</div>