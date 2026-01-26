<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi P2K</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/kunjunganuser.css') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .btn-excel { cursor: pointer; transition: 0.3s; }
        .btn-excel:hover { background-color: #16a34a !important; }
        .nav-item { cursor: pointer; }
        .status-circle { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; }
        .bg-success { background-color: #22c55e; color: white; }
        .bg-pending { border: 2px solid #1e293b; color: #1e293b; }
    </style>
</head>
<body>

    <div class="wrapper">
        <div class="sidebar">
            <h1>Menu</h1>
            <a href="javascript:void(0)" onclick="loadPage('data-kunjungan')" class="nav-item active" id="menu-data">
                <i class="fa-solid fa-user-plus"></i> Data Kunjungan
            </a>
            <a href="javascript:void(0)" onclick="loadPage('laporan-kunjungan')" class="nav-item" id="menu-laporan">
                <i class="fa-solid fa-file-lines"></i> Laporan Kunjungan
            </a>
            <a href="javascript:void(0)" onclick="loadPage('dokumen')" class="nav-item" id="menu-dokumen">
                <i class="fa-solid fa-file-invoice"></i> Dokumen
            </a>
            <a href="javascript:void(0)" onclick="loadPage('pengaturan')" class="nav-item" id="menu-pengaturan">
                <i class="fa-solid fa-gear"></i> Pengaturan
            </a>
        </div>

        <div class="main-container" style="flex: 1; display: flex; flex-direction: column;">
            <div class="header">
                <div class="logo-section">
                    <img src="{{ asset('assets/logo.png') }}" alt="Logo">
                    <span>Sistem Informasi<br>P2K</span>
                </div>
               <div class="user-profile" style="position: relative; cursor: pointer;" onclick="toggleDropdown()">
                    <span style="font-weight: 700;">{{ Auth::guard('karyawan')->user()->nama }}</span>
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::guard('karyawan')->user()->nama) }}&background=4e4bc1&color=fff" alt="User">
                    <i class="fa-solid fa-chevron-down" style="font-size: 12px; margin-left: 5px; color: #666;"></i>

                    <div id="userDropdown" style="display: none; position: absolute; right: 0; top: 50px; width: 150px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1000; border: 1px solid #eee;">
                        <ul style="list-style: none; margin: 0; padding: 0;">
                            <li style="padding: 10px 15px; border-bottom: 1px solid #eee; font-size: 14px; color: #333;">
                                <i class="fa-solid fa-user" style="margin-right: 8px;"></i> Profil
                            </li>
                            <li style="padding: 0;">
                                <form action="{{ route('logout') }}" method="POST" id="logout-form">
                                    @csrf
                                    <button type="submit" style="width: 100%; text-align: left; padding: 10px 15px; background: none; border: none; color: #e74c3c; cursor: pointer; font-family: inherit; font-size: 14px; font-weight: 600;">
                                        <i class="fa-solid fa-right-from-bracket" style="margin-right: 8px;"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="main-content">
                <div id="konten-utama">
                    @include('kunjungan.partials.data_table')
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        Sistem Aplikasi P2K
    </div>

    @include('kunjungan.partials.modals')

    <script>
        // --- Fungsi Navigasi Menu Utama ---
        function loadPage(pageName) {
            const contentArea = document.getElementById('konten-utama');
            contentArea.style.opacity = '0.3';

            fetch(`/user/${pageName}-content`) 
                .then(response => {
                    if (!response.ok) throw new Error('Gagal memuat halaman');
                    return response.text();
                })
                .then(html => {
                    contentArea.innerHTML = html;
                    contentArea.style.opacity = '1';
                    updateSidebarActive(pageName); // Panggil fungsi di sini
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('Kesalahan: ' + error.message);
                    contentArea.style.opacity = '1';
                });
        }

        function loadDashboardGrafik() {
            if (typeof loadAdminPage === "function") {
                loadAdminPage('dashboard'); 
            } else {
                loadPage('dashboard');
            }
        }

        // --- Fungsi Khusus Load Detail/Bukti (Tombol Check) ---
        function loadContent(url) {
            const contentArea = document.getElementById('konten-utama');
            contentArea.style.opacity = '0.3';

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    // Mengisi ID konten-utama dengan hasil HTML bukti kunjungan
                    $('#konten-utama').html(response); 
                    contentArea.style.opacity = '1';
                    console.log("Konten Bukti Kunjungan berhasil dimuat.");
                },
                error: function(xhr) {
                    contentArea.style.opacity = '1';
                    console.error("Gagal AJAX: ", xhr.responseText);
                    alert('Gagal memuat halaman bukti.');
                }
            });
        }

     function updateSidebarActive(pageName) {
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            
            let targetId = '';
            if (pageName === 'laporan-kunjungan') {
                targetId = 'menu-laporan';
            } else if (pageName === 'dokumen') {
                targetId = 'menu-dokumen';
            } else if (pageName === 'pengaturan') { 
                targetId = 'menu-pengaturan';
            } else {
                targetId = 'menu-data';
            }

            const activeEl = document.getElementById(targetId);
            if (activeEl) {
                activeEl.classList.add('active');
            }
        }

        // --- Fungsi Modal Form Kunjungan ---
        function openModal(nama, kode) {
            document.getElementById('form-no-nasabah').value = kode;
            document.getElementById('form-nama-nasabah').value = nama;
            document.getElementById('display-no').value = kode;
            document.getElementById('display-nama').value = nama;
            document.getElementById('visitModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('visitModal').style.display = 'none';
        }

        function openDetailModal(kode, angsuran, nama, alamat, nominal, sisa, kol, kodeAo, namaAo) {
            try {
                document.getElementById('detail-kode').innerText = kode || '-';
                document.getElementById('detail-angsuran').innerText = angsuran || '-';
                document.getElementById('detail-nama').innerText = nama || '-';
                document.getElementById('detail-alamat').innerText = alamat || '-';
                document.getElementById('detail-nominal').innerText = nominal || '-';
                document.getElementById('detail-sisa').innerText = sisa || '-';
                document.getElementById('detail-kol').innerText = kol || '-';
                document.getElementById('detail-kode-ao').innerText = kodeAo || '-';
                document.getElementById('detail-nama-ao').innerText = namaAo || '-';
                document.getElementById('detailModal').style.display = 'flex';
            } catch (error) {
                console.error("Error modal:", error);
            }
        }

        function closeDetailModal() {
            document.getElementById('detailModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                closeModal();
                closeDetailModal();
            }
        }
    </script>

    <script>
        function switchSettingsTab(tab) {
            const secAkun = document.getElementById('section-akun');
            const secSandi = document.getElementById('section-sandi');
            const btnAkun = document.getElementById('tab-btn-akun');
            const btnSandi = document.getElementById('tab-btn-sandi');

            if (!secAkun || !secSandi) return;

            if (tab === 'akun') {
                secAkun.style.display = 'block';
                secSandi.style.display = 'none';
                btnAkun.style.background = '#adc7ff'; 
                btnAkun.style.color = '#3f36b1';
                btnSandi.style.background = 'transparent'; 
                btnSandi.style.color = '#64748b';
            } else {
                secSandi.style.display = 'block';
                secAkun.style.display = 'none';
                btnSandi.style.background = '#adc7ff'; 
                btnSandi.style.color = '#3f36b1';
                btnAkun.style.background = 'transparent'; 
                btnAkun.style.color = '#64748b';
            }
        }
    </script>

    <script>
    document.addEventListener('click', function (e) {
        
        // --- 1. Konfirmasi Hapus Avatar ---
        const deleteAvatarBtn = e.target.closest('.btn-settings-cancel'); // Tombol "Hapus Avatar"
        if (deleteAvatarBtn && deleteAvatarBtn.innerText.includes('Hapus Avatar')) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Hapus Foto Profil?',
                text: "Foto profil Anda akan dikembalikan ke avatar default",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33', // Warna merah untuk aksi hapus
                cancelButtonColor: '#cbd5e1',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Simulasi reset gambar ke default ui-avatars
                    const profileImg = document.querySelector('.profile-avatar-img');
                    if(profileImg) {
                        profileImg.src = "https://ui-avatars.com/api/?name=User&background=0D8ABC&color=fff&size=120";
                    }

                    Swal.fire({
                        title: 'Terhapus!',
                        text: 'Foto profil telah dihapus.',
                        icon: 'success',
                        confirmButtonColor: '#3f36b1'
                    });
                }
            });
        }

        // --- 2. Alert Simpan Akun ---
        const saveAccountBtn = e.target.closest('#section-akun .btn-settings-save');
        if (saveAccountBtn && !saveAccountBtn.innerText.includes('Upload')) { 
            e.preventDefault();
            Swal.fire({
                title: 'Simpan Perubahan Akun?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3f36b1',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Berhasil!', 'Data akun telah diperbarui.', 'success');
                }
            });
        }

        // --- 3. Alert Simpan Kata Sandi ---
        const savePassBtn = e.target.closest('#section-sandi .btn-settings-save');
        if (savePassBtn) {
            e.preventDefault();
            Swal.fire({
                title: 'Simpan Perubahan Kata Sandi?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3f36b1',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Berhasil!', 'Kata sandi telah diperbarui.', 'success');
                    document.querySelectorAll('#section-sandi input').forEach(input => input.value = '');
                }
            });
        }
    });
</script>
    
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const targetPage = urlParams.get('page');
    
    if (targetPage) {
        loadPage(targetPage);
    }
});
</script>
   
<script>
    function toggleDropdown() {
        const dropdown = document.getElementById("userDropdown");
        if (dropdown.style.display === "none" || dropdown.style.display === "") {
            dropdown.style.display = "block";
        } else {
            dropdown.style.display = "none";
        }
    }

    window.onclick = function(event) {
        if (!event.target.closest('.user-profile')) {
            const dropdown = document.getElementById("userDropdown");
            if (dropdown) {
                dropdown.style.display = "none";
            }
        }
    }
</script>

<script>
// Fungsi Switch Tab (Akun / Sandi)
function switchSettingsTab(tab) {
    const btnAkun = document.getElementById('tab-btn-akun');
    const btnSandi = document.getElementById('tab-btn-sandi');
    const secAkun = document.getElementById('section-akun');
    const secSandi = document.getElementById('section-sandi');

    if (tab === 'akun') {
        btnAkun.classList.add('tab-active');
        btnSandi.classList.remove('tab-active');
        secAkun.style.display = 'block';
        secSandi.style.display = 'none';
    } else {
        btnSandi.classList.add('tab-active');
        btnAkun.classList.remove('tab-active');
        secSandi.style.display = 'block';
        secAkun.style.display = 'none';
    }
}

// Fungsi Simpan Profil Akun
async function simpanAkun() {
    const nama = document.querySelector('input[name="nama"]').value;
    const no_hp = document.querySelector('input[name="no_hp"]').value;
    const _token = document.querySelector('input[name="_token"]').value;

    try {
        const response = await fetch("{{ route('settings.akun') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': _token
            },
            body: JSON.stringify({ nama, no_hp })
        });

        const res = await response.json();
        if(response.ok) {
            alert(res.success);
            // Opsional: Update nama di pojok kanan atas secara instan
            document.querySelector('.user-profile span').innerText = nama;
        } else {
            alert("Terjadi kesalahan.");
        }
    } catch (e) {
        alert("Gagal terhubung ke server.");
    }
}

// Fungsi Simpan Sandi
async function simpanSandi() {
    const current_password = document.querySelector('input[name="current_password"]').value;
    const new_password = document.querySelector('input[name="new_password"]').value;
    const _token = document.querySelector('input[name="_token"]').value;

    const response = await fetch("{{ route('settings.sandi') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': _token
        },
        body: JSON.stringify({ current_password, new_password })
    });

    const res = await response.json();
    if(response.ok) {
        alert(res.success);
        // Kosongkan input setelah berhasil
        document.querySelectorAll('input[type="password"]').forEach(el => el.value = '');
    } else {
        alert(res.error || "Gagal ganti sandi.");
    }
}

window.fileSiapUpload = null;

window.previewAvatar = function(input) {
    if (input.files && input.files[0]) {
        window.fileSiapUpload = input.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            document.getElementById('display-avatar').src = e.target.result;
        }
        
        reader.readAsDataURL(window.fileSiapUpload);
    }
}

window.simpanAvatarKeServer = async function() {
    if (!window.fileSiapUpload) {
        alert("Pilih foto terlebih dahulu dengan mengklik logo kamera!");
        return;
    }

    const formData = new FormData();
    formData.append('avatar', window.fileSiapUpload);
    formData.append('_token', document.querySelector('input[name="_token"]').value);

    try {
        const response = await fetch("{{ route('settings.avatar') }}", {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const result = await response.json();

        if (response.ok) {
            alert('Avatar berhasil diperbarui!');
            document.querySelectorAll('.user-profile img').forEach(img => img.src = result.url);
            window.fileSiapUpload = null;
        } else {
            alert(result.error || 'Gagal mengupload foto.');
        }
    } catch (error) {
        alert('Terjadi kesalahan jaringan.');
    }
}

let fileSiapUpload = null;

// Fungsi 1: Hanya menampilkan pratinjau (Preview)
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        fileSiapUpload = input.files[0]; // Simpan file ke variabel
        const reader = new FileReader();
        
        reader.onload = function(e) {
            document.getElementById('display-avatar').src = e.target.result;
        }
        
        reader.readAsDataURL(fileSiapUpload);
    }
}

window.simpanAvatarKeServer = async function() {
    if (!window.fileSiapUpload) {
        alert("Pilih foto terlebih dahulu!");
        return;
    }

    const formData = new FormData();
    formData.append('avatar', window.fileSiapUpload);
    
    // Ambil token dari meta tag yang kita pasang di head tadi
    const token = document.querySelector('meta[name="csrf-token"]').content;

    try {
        const response = await fetch("{{ route('settings.avatar') }}", {
            method: 'POST',
            body: formData,
            headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token // Kirim token via Header
            }
        });

        const result = await response.json();
        if (response.ok) {
            alert(result.success);
            const newUrl = result.url + '?t=' + new Date().getTime();
            document.getElementById('display-avatar').src = newUrl;
            document.querySelectorAll('.user-profile img').forEach(img => img.src = newUrl);
            window.fileSiapUpload = null;
        } else {
            alert(result.error || 'Gagal menyimpan.');
        }
    } catch (e) { alert("Terjadi kesalahan sistem."); }
}

// Fungsi 3: Reset jika tidak jadi upload
function resetAvatarPreview() {
    const defaultAvatar = "{{ Auth::guard('karyawan')->user()->avatar ? asset('storage/' . Auth::guard('karyawan')->user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::guard('karyawan')->user()->nama) . '&background=0D8ABC&color=fff&size=120' }}";
    document.getElementById('display-avatar').src = defaultAvatar;
    document.getElementById('upload-avatar-input').value = "";
    fileSiapUpload = null;
}
</script>



</body>
</html>