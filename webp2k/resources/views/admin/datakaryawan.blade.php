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
                
                <a href="javascript:void(0)" 
                    onclick="loadAdminPage('data-karyawan', this)" class="nav-item {{ request()->is('admin/karyawan*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i> Data Karyawan
                </a>

                <a href="javascript:void(0)" 
                    onclick="loadAdminPage('adm-kunjungan', this)" class="nav-item {{ request()->is('admin/kunjungan*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-check"></i> Data Kunjungan
                </a>
                
                <a href="javascript:void(0)" 
                    onclick="loadAdminPage('nasabah', this)" class="nav-item {{ request()->is('admin/nasabah*') ? 'active' : '' }}" id="menu-nasabah">
                    <i class="fa-solid fa-address-card"></i> Data Nasabah
                </a>

                <a href="javascript:void(0)" 
                    onclick="loadAdminPage('pelaporan', this)" class="nav-item {{ request()->is('pelaporan*') ? 'active' : '' }}" id="menu-pelaporan">
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
            function loadAdminPage(pageName, element) {
            const url = '/' + pageName + '-content'; 
            
            // 1. Reset semua menu yang aktif
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });

            // 2. Logika Penentuan Menu Aktif
            if (element) {
                // Jika diklik langsung dari sidebar
                element.classList.add('active');
            } 
            else if (pageName === 'detail-kunjungan') {
                // Pengecualian untuk detail kunjungan
                const kunjunganMenu = document.querySelector('a[onclick*="adm-kunjungan"]');
                if (kunjunganMenu) kunjunganMenu.classList.add('active');
            } 
            else if (pageName === 'pengunjung-nasabah' || pageName === 'nasabah') {
                // PERBAIKAN: Gunakan ID menu-nasabah yang sudah Anda buat di sidebar
                const nasabahMenu = document.getElementById('menu-nasabah');
                if (nasabahMenu) {
                    nasabahMenu.classList.add('active');
                }
            }

            // 3. Eksekusi Fetch Konten
            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Status: ' + response.status);
                    return response.text();
                })
                .then(html => {
                    document.getElementById('konten-admin').innerHTML = html;
                })
                .catch(err => console.error("Gagal load:", err));
        }
        function setActiveMenuOnLoad() {
            const currentPath = window.location.pathname;
            const navItems = document.querySelectorAll('.nav-item');

            navItems.forEach(item => {
                if (currentPath.includes('karyawan') && item.innerText.includes('Karyawan')) {
                    item.classList.add('active');
                } else if (currentPath.includes('kunjungan') && item.innerText.includes('Kunjungan')) {
                    item.classList.add('active');
                }
            });
        }

        window.addEventListener('DOMContentLoaded', setActiveMenuOnLoad);

      function updateAdminSidebar(pageName) {
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });

            const activeMenu = document.getElementById(`menu-${pageName}`);
            if (activeMenu) {
                activeMenu.classList.add('active');
            }
        }

        const modalEdit = document.getElementById('modalEditKaryawan');

        function openModalEdit() {
            if (modalEdit) {
                modalEdit.style.display = 'flex';
            }
        }

        function closeModalEdit() {
            if (modalEdit) {
                modalEdit.style.display = 'none';
            }
        }

        window.addEventListener('click', function(event) {
            if (event.target === modalEdit) {
                closeModalEdit();
            }
        });

        document.querySelector('.dropdown-toggle').addEventListener('click', function() {
            const dropdown = document.getElementById('dropdown-input');
            const arrow = this.querySelector('.arrow-icon');
            
            dropdown.classList.toggle('show');
            arrow.classList.toggle('rotate');
        });

       function openModalTambah() {
            const modal = document.getElementById('modalTambahKaryawan');
            if (modal) {
                modal.style.display = 'flex'; 
            }
        }

        function closeModalTambah() {
            const modal = document.getElementById('modalTambahKaryawan');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        const modalDetail = document.getElementById('modalDetailKaryawan');

        function openModalDetail() {
            if (modalDetail) {
                modalDetail.style.display = 'flex';
            }
        }

        function closeModalDetail() {
            if (modalDetail) {
                modalDetail.style.display = 'none';
            }
        }

        window.addEventListener('click', function(event) {
            if (event.target === modalDetail) {
                closeModalDetail();
            }
        });

        function openModalExport() {
            $('#modalExportNasabah').fadeIn(); 
        }

        function closeModalExport() {
            $('#modalExportNasabah').fadeOut();
        }

        function openModalFilter() {
            $('#modalFilterNasabah').fadeIn();
        }

        function closeModalFilter() {
            $('#modalFilterNasabah').fadeOut();
        }

        function openExportModal() {
            const modal = document.getElementById('modalExportPelaporan');
            if (modal) {
                modal.style.display = 'flex';
            }
        }


        window.addEventListener('click', function(event) {
            const modal = document.getElementById('modalExportPelaporan');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });

        $(window).on('click', function(event) {
            if ($(event.target).is('.modal-overlay')) {
                closeModalExport();
                closeModalFilter();
            }
        });
                

    </script>
</body>
</html>