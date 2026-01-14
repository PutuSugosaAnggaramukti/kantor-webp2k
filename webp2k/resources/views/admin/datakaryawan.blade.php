<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Sistem Informasi P2K</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/karyawan.css') }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

   <div class="wrapper">
        <div class="sidebar">
                <h2>Menu</h2>
                
                <a href="javascript:void(0)" onclick="loadAdminPage('data-karyawan')" class="nav-item active" id="menu-karyawan">
                    <i class="fa-solid fa-users"></i> Data Karyawan
                </a>
                
                <a href="javascript:void(0)" onclick="loadAdminPage('data-kunjungan')" class="nav-item" id="menu-kunjungan">
                    <i class="fa-solid fa-clipboard-check"></i> Data Kunjungan
                </a>
                
                <a href="javascript:void(0)" onclick="loadAdminPage('data-nasabah')" class="nav-item" id="menu-nasabah">
                    <i class="fa-solid fa-address-card"></i> Data Nasabah
                </a>

                <a href="javascript:void(0)" onclick="loadAdminPage('pelaporan')" class="nav-item" id="menu-pelaporan">
                    <i class="fa-solid fa-file-signature"></i> Pelaporan
                </a>

                <a href="javascript:void(0)" onclick="loadAdminPage('dokumen')" class="nav-item" id="menu-dokumen">
                    <i class="fa-solid fa-file-lines"></i> Dokumen
                </a>

                <div class="dropdown-wrapper">
                    <a href="javascript:void(0)" class="nav-item dropdown-toggle" id="menu-input-data">
                        <div style="display: flex; align-items: center;">
                            <i class="fa-solid fa-file-circle-plus"></i> 
                            <span>Input Data Kunjungan</span>
                        </div>
                        <i class="fa-solid fa-chevron-down arrow-icon" style="font-size: 14px; margin-right: 10px;"></i> 
                    </a>
                    
                    <div class="dropdown-container" id="dropdown-input">
                        <a href="javascript:void(0)" onclick="loadAdminPage('jadwal')" class="sub-nav-item" id="menu-jadwal">
                            <i class="fa-solid fa-calendar-days"></i> Jadwal Kunjungan
                        </a>
                        
                        <a href="javascript:void(0)" onclick="loadAdminPage('input-baru')" class="sub-nav-item">
                            <i class="fa-solid fa-circle-plus"></i> Tambah Data
                        </a>
                    </div>
                </div>
            </div>
        <div class="main-container">
            
            <div class="header">
                <div class="logo-section">
                    <img src="{{ asset('assets/logo.png') }}" alt="Logo">
                    <span>Sistem Informasi<br>P2K</span>
                </div>
                <div class="user-profile">
                    <span>Admin</span>
                    <img src="https://i.pravatar.cc/150?u=admin" alt="User">
                </div>
            </div>

            <div class="main-content">
                <div class="content-padding">
                    <div id="konten-admin">
                        @include('admin.partials.karyawan_table')
                    </div>
                </div>
            </div>
        </div>
    </div>
      

    <footer class="main-footer">
        <p>Sistem Aplikasi P2K</p>
    </footer>

    @include('admin.partials.modals')

    <script>
        // Fungsi Navigasi SPA Admin
        function loadAdminPage(pageName) {
            const contentArea = document.getElementById('konten-admin');
            contentArea.style.opacity = '0.3';

            // Mengambil konten dari route Laravel yang mengembalikan view partial
            fetch(`/admin/${pageName}-content`) 
                .then(response => response.text())
                .then(html => {
                    contentArea.innerHTML = html;
                    contentArea.style.opacity = '1';
                    updateAdminSidebar(pageName);
                })
                .catch(err => console.error(err));
        }

      function updateAdminSidebar(pageName) {
            // 1. Hapus class 'active' dari semua item menu
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });

            // 2. Cari elemen berdasarkan ID dan tambahkan class 'active'
            // Pastikan ID di HTML sesuai (contoh: menu-data-karyawan)
            const activeMenu = document.getElementById(`menu-${pageName}`);
            if (activeMenu) {
                activeMenu.classList.add('active');
            }
        }

        // Fungsi Modal Edit Karyawan (Sesuai image_1225f4.png)
        function openEditModal(kode, nama) {
            document.getElementById('edit-kode').value = kode;
            document.getElementById('edit-nama').value = nama;
            document.getElementById('modalEditKaryawan').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('modalEditKaryawan').style.display = 'none';
        }

        document.querySelector('.dropdown-toggle').addEventListener('click', function() {
            const dropdown = document.getElementById('dropdown-input');
            const arrow = this.querySelector('.arrow-icon');
            
            dropdown.classList.toggle('show');
            arrow.classList.toggle('rotate');
        });

    </script>
</body>
</html>