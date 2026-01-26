<div class="page-title" style="margin-bottom: 20px;">
    <h2>Pengaturan</h2>
    <div class="breadcrumb">
        <a href="/user/dashboard">Dashboard > </a> <span style="color: #3b82f6;">Pengaturan</span>
    </div>
</div>

<div class="settings-container">
    <div class="settings-sidebar">
        <div id="tab-btn-akun" onclick="switchSettingsTab('akun')" class="tab-setting-item tab-active">
            <i class="fa-solid fa-user-circle"></i> Pengaturan Akun
        </div>
        <div id="tab-btn-sandi" onclick="switchSettingsTab('sandi')" class="tab-setting-item">
            <i class="fa-solid fa-lock"></i> Kata Sandi
        </div>
    </div>

    <div class="settings-content-area">
        <div id="section-akun" style="display: block;">
            <div class="avatar-upload-section" style="display: flex; align-items: center; gap: 30px; margin-bottom: 40px;">
                <div style="position: relative; width: 120px; height: 120px;">
                    <img id="display-avatar" 
                        src="{{ Auth::guard('karyawan')->user()->avatar ? asset('storage/' . Auth::guard('karyawan')->user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::guard('karyawan')->user()->nama) . '&background=0D8ABC&color=fff&size=120' }}" 
                        class="profile-avatar-img" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover;">
                    
                    <input type="file" id="upload-avatar-input" hidden accept="image/*" onchange="previewAvatar(this)">
                    
                    <div class="avatar-camera-icon" onclick="document.getElementById('upload-avatar-input').click()" 
                        style="position: absolute; bottom: 5px; right: 5px; background: #3b82f6; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; cursor: pointer;">
                        <i class="fa-solid fa-camera" style="font-size: 14px;"></i>
                    </div>
                </div>

                <div style="display: flex; gap: 12px;">
                    <button type="button" onclick="simpanAvatarKeServer()" class="btn-settings-save" style="padding: 10px 20px;">
                        Upload Avatar
                    </button>
                    <button type="button" onclick="resetAvatarPreview()" class="btn-settings-cancel" style="padding: 10px 20px;">
                        Hapus Avatar
                    </button>
                </div>
            </div>

            <div class="settings-group">
                <label>Username (Read Only)</label>
                <input type="text" value="{{ Auth::guard('karyawan')->user()->username }}" class="settings-input" readonly style="background: #f5f5f5;">
            </div>
            <div class="settings-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" value="{{ Auth::guard('karyawan')->user()->nama }}" class="settings-input">
            </div>
            <div class="settings-group">
                <label>Kode AO</label>
                <input type="text" value="{{ Auth::guard('karyawan')->user()->kode_ao }}" class="settings-input" readonly style="background: #f5f5f5;">
            </div>
            <div class="settings-group">
                <label>Email</label>
                <input type="email" placeholder="Masukkan email" class="settings-input">
            </div>
            
            <div class="form-actions" style="display: flex; gap: 15px; margin-top: 20px;">
                <button class="btn-settings-save">Simpan</button>
                <button class="btn-settings-cancel">Batal</button>
            </div>
        </div>

        <div id="section-sandi" style="display: none;">
            <div class="settings-group">
                <label>Kata Sandi Sekarang</label>
                <input type="password" name="current_password" class="settings-input" placeholder="********">
            </div>
            <div class="settings-group">
                <label>Kata Sandi Baru</label>
                <input type="password" name="new_password" class="settings-input" placeholder="********">
            </div>
            <div class="form-actions-centered" style="display: flex; justify-content: center; gap: 50px; margin-top: 20px;">
                <button type="submit" class="btn-settings-save">Simpan</button>
                <button type="button" onclick="switchSettingsTab('akun')" class="btn-settings-cancel">Batal</button>
            </div>
        </div>
    </div>
</div>


