<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi P2K</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/kunjunganuser.css') }}">

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
                <div class="user-profile">
                    <span>Username</span>
                    <img src="https://i.pravatar.cc/150?u=admin" alt="User">
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
        2025 Sistem Aplikasi P2K
    </div>

    @include('kunjungan.partials.modals')

    <script>
        // --- Fungsi Navigasi Menu Utama ---
        function loadPage(pageName) {
            const contentArea = document.getElementById('konten-utama');
            contentArea.style.opacity = '0.3';

            fetch(`/${pageName}-content`) 
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
    
   

</body>
</html>