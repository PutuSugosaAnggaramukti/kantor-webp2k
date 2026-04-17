<div class="page-title" style="margin-bottom: 20px;">
    <h2>Pengaturan</h2>
    <div class="breadcrumb">
        <a href="/admin/dashboard">Dashboard > </a> <span style="color: #3b82f6;">Ganti Kata Sandi</span>
    </div>
</div>

<div class="settings-container" style="display: flex; justify-content: center;">
    <div class="settings-content-area" style="width: 100%; max-width: 600px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        
        <div id="section-sandi">
            <h3 style="margin-bottom: 20px; color: #333;">Ubahlah Kata Sandi Admin Disini</h3>
            
           <div class="settings-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Kata Sandi Sekarang</label>
                <div style="position: relative; width: 100%;">
                    <input type="password" id="current_password" name="current_password" class="settings-input" placeholder="********" 
                        style="width: 100%; padding: 12px; padding-right: 45px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box;">
                    <i class="fa-solid fa-eye" id="toggleCurrentPassword" 
                    style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666;"></i>
                </div>
            </div>

            <div class="settings-group" style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Kata Sandi Baru</label>
                <div style="position: relative; width: 100%;">
                    <input type="password" id="new_password" name="new_password" class="settings-input" placeholder="********" 
                        style="width: 100%; padding: 12px; padding-right: 45px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box;">
                    <i class="fa-solid fa-eye" id="toggleNewPassword" 
                    style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666;"></i>
                </div>
            </div>

            <div class="form-actions-centered" style="display: flex; gap: 15px;">
                <button type="button" onclick="updateSandiAdmin()" class="btn-settings-save" style="flex: 1; padding: 12px; background: #3b82f6; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                    <i class="fa-solid fa-save" style="margin-right: 8px;"></i> Simpan Perubahan
                </button>
                <button type="button" onclick="loadAdminPage('dashboard-content', this)" class="btn-settings-cancel" style="flex: 1; padding: 12px; background: #f1f5f9; color: #475569; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>